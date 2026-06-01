<?php

declare(strict_types=1);

/*
 * This file is part of the Modelflow AI package.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ModelflowAi\GoogleGeminiAdapter\Chat;

use Gemini\Contracts\ClientContract;
use Gemini\Contracts\Resources\GenerativeModelContract;
use Gemini\Data\Blob;
use Gemini\Data\Content;
use Gemini\Data\FunctionCall;
use Gemini\Data\FunctionCallingConfig;
use Gemini\Data\FunctionResponse;
use Gemini\Data\GenerationConfig;
use Gemini\Data\Part;
use Gemini\Data\Schema;
use Gemini\Data\ToolConfig;
use Gemini\Enums\DataType;
use Gemini\Enums\MimeType;
use Gemini\Enums\Mode;
use Gemini\Enums\ResponseMimeType;
use Gemini\Enums\Role;
use Gemini\Responses\GenerativeModel\GenerateContentResponse;
use ModelflowAi\Chat\Adapter\AIChatAdapterInterface;
use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\Chat\Request\AIChatStreamedRequest;
use ModelflowAi\Chat\Request\Message\AIChatMessage;
use ModelflowAi\Chat\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Chat\Request\Message\ImageBase64Part;
use ModelflowAi\Chat\Request\Message\TextPart;
use ModelflowAi\Chat\Request\Message\ToolCallPart;
use ModelflowAi\Chat\Request\Message\ToolCallsPart;
use ModelflowAi\Chat\Request\ResponseFormat\JsonResponseFormat;
use ModelflowAi\Chat\Request\ResponseFormat\JsonSchemaResponseFormat;
use ModelflowAi\Chat\Request\ResponseFormat\ResponseFormatInterface;
use ModelflowAi\Chat\Request\ResponseFormat\SupportsResponseFormatInterface;
use ModelflowAi\Chat\Response\AIChatResponse;
use ModelflowAi\Chat\Response\AIChatResponseMessage;
use ModelflowAi\Chat\Response\AIChatResponseStream;
use ModelflowAi\Chat\Response\AIChatToolCall;
use ModelflowAi\Chat\Response\StreamingUsageTracker;
use ModelflowAi\Chat\Response\Usage;
use ModelflowAi\Chat\ToolInfo\ToolChoiceEnum;
use ModelflowAi\Chat\ToolInfo\ToolTypeEnum;
use Webmozart\Assert\Assert;

final readonly class GoogleGeminiChatAdapter implements AIChatAdapterInterface, SupportsResponseFormatInterface
{
    public const EXPECTED_ROLES = [
        AIChatMessageRoleEnum::SYSTEM,
        AIChatMessageRoleEnum::ASSISTANT,
        AIChatMessageRoleEnum::USER,
        AIChatMessageRoleEnum::TOOL,
    ];

    public function __construct(
        private ClientContract $client,
        private string $model,
    ) {
    }

    public function handleRequest(AIChatRequest $request): AIChatResponse
    {
        if ($request->getOption('seed')) {
            @\trigger_error('Seed option is not supported by Google Gemini.', \E_USER_WARNING);
        }

        $messages = [];
        /** @var AIChatMessage $aiMessage */
        foreach ($request->getMessages() as $aiMessage) {
            if (!\in_array($aiMessage->role, self::EXPECTED_ROLES, true)) {
                throw new \Exception('Not supported message role.');
            }

            $parts = [];

            foreach ($aiMessage->parts as $part) {
                if ($part instanceof TextPart) {
                    $parts[] = new Part(text: $part->text);
                } elseif ($part instanceof ImageBase64Part) {
                    $parts[] = new Part(inlineData: new Blob(MimeType::from($part->mimeType), $part->content));
                } elseif ($part instanceof ToolCallsPart) {
                    foreach ($part->toolCalls as $toolCall) {
                        $parts[] = new Part(functionCall: new FunctionCall(
                            name: $toolCall->name,
                            args: $toolCall->arguments,
                            id: '' !== $toolCall->id ? $toolCall->id : null,
                        ));
                    }
                } elseif ($part instanceof ToolCallPart) {
                    $parts[] = new Part(functionResponse: new FunctionResponse(
                        name: $part->toolName,
                        response: $this->decodeToolResult($part->content),
                        id: '' !== $part->toolCallId ? $part->toolCallId : null,
                    ));
                } else {
                    throw new \Exception('Not supported message part type.');
                }
            }

            $geminiRole = match ($aiMessage->role) {
                AIChatMessageRoleEnum::USER, AIChatMessageRoleEnum::TOOL => Role::USER,
                default => Role::MODEL,
            };

            $messages[] = new Content(parts: $parts, role: $geminiRole);
        }

        $responseFormat = $request->getResponseFormat();
        $config = $this->buildGenerationConfig($request, $responseFormat);

        $model = $this->client->generativeModel($this->model);
        $model = $model->withGenerationConfig($config);

        if ($request->hasTools()) {
            $model = $model->withTool(ToolFormatter::formatTools($request->getToolInfos()));
            $model = $model->withToolConfig(new ToolConfig(
                functionCallingConfig: new FunctionCallingConfig(
                    mode: ToolChoiceEnum::NONE === $request->getToolChoice() ? Mode::NONE : Mode::AUTO,
                ),
            ));
        }

        if ($request instanceof AIChatStreamedRequest) {
            return $this->createStreamed($request, $messages, $model);
        }

        return $this->create($request, $messages, $model);
    }

    private function buildGenerationConfig(AIChatRequest $request, ?ResponseFormatInterface $responseFormat): GenerationConfig
    {
        $temperature = $request->getOption('temperature');
        $responseMimeType = null;
        $responseSchema = null;

        if ($responseFormat instanceof JsonSchemaResponseFormat) {
            $responseMimeType = ResponseMimeType::APPLICATION_JSON;
            $responseSchema = $this->convertSchema($responseFormat->schema);
        } elseif ($responseFormat instanceof JsonResponseFormat) {
            $responseMimeType = ResponseMimeType::APPLICATION_JSON;
        }

        if (null !== $temperature) {
            Assert::float($temperature);

            return new GenerationConfig(
                temperature: $temperature,
                responseMimeType: $responseMimeType,
                responseSchema: $responseSchema,
            );
        }

        if ($responseMimeType instanceof ResponseMimeType || $responseSchema instanceof Schema) {
            return new GenerationConfig(
                responseMimeType: $responseMimeType,
                responseSchema: $responseSchema,
            );
        }

        return new GenerationConfig();
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function convertSchema(array $schema): Schema
    {
        $typeValue = \is_string($schema['type'] ?? null) ? $schema['type'] : 'object';
        $type = DataType::from(\strtoupper($typeValue));
        $description = \is_string($schema['description'] ?? null) ? $schema['description'] : null;
        /** @var array<string>|null $required */
        $required = \is_array($schema['required'] ?? null) ? $schema['required'] : null;
        $properties = null;
        $itemsSchema = null;

        if (isset($schema['properties']) && \is_array($schema['properties'])) {
            /** @var array<string, mixed> $schemaProperties */
            $schemaProperties = $schema['properties'];
            $properties = [];
            foreach ($schemaProperties as $name => $property) {
                if (\is_array($property)) {
                    /** @var array<string, mixed> $property */
                    $properties[$name] = $this->convertSchema($property);
                }
            }
        }

        if (isset($schema['items']) && \is_array($schema['items'])) {
            /** @var array<string, mixed> $schemaItems */
            $schemaItems = $schema['items'];
            $itemsSchema = $this->convertSchema($schemaItems);
        }

        return new Schema(
            type: $type,
            description: $description,
            properties: $properties,
            required: $required,
            items: $itemsSchema,
        );
    }

    /**
     * @param Content[] $messages
     */
    private function create(AIChatRequest $request, array $messages, GenerativeModelContract $model): AIChatResponse
    {
        $result = $model->generateContent(...$messages);

        $toolCalls = $this->extractToolCalls($result);

        if ([] === $toolCalls) {
            // Pure text response: keep the strict single-part accessor so blocked prompts still surface.
            try {
                $text = $result->text();
            } catch (\ValueError $exception) {
                throw new \RuntimeException(
                    message: \sprintf('Request blocked by safety settings: %s', $exception->getMessage()),
                    code: $exception->getCode(),
                    previous: $exception,
                );
            }
        } else {
            // Tool-call responses are not simple text (functionCall parts, optionally mixed with text),
            // so gather any text parts without tripping the single-part accessor.
            $text = $this->extractText($result);
        }

        if (\str_starts_with($text, '```json') && \str_ends_with($text, '```')) {
            $text = \substr($text, 7, -3);
        }

        return new AIChatResponse(
            $request,
            new AIChatResponseMessage(
                AIChatMessageRoleEnum::ASSISTANT,
                $text,
                $toolCalls,
            ),
            new Usage(
                $result->usageMetadata->promptTokenCount,
                $result->usageMetadata->totalTokenCount - $result->usageMetadata->promptTokenCount,
                $result->usageMetadata->totalTokenCount,
            ),
        );
    }

    /**
     * Extract function calls from the first candidate of the response.
     *
     * Gemini does not always return an id for a function call; fall back to the function name,
     * which is also what the API uses to correlate the matching functionResponse.
     *
     * @return AIChatToolCall[]
     */
    private function extractToolCalls(GenerateContentResponse $result): array
    {
        $toolCalls = [];
        foreach ($result->candidates as $candidate) {
            foreach ($candidate->content->parts as $part) {
                if (!$part->functionCall instanceof FunctionCall) {
                    continue;
                }

                $toolCalls[] = new AIChatToolCall(
                    ToolTypeEnum::FUNCTION,
                    $part->functionCall->id ?? $part->functionCall->name,
                    $part->functionCall->name,
                    $part->functionCall->args,
                );
            }

            // The quick accessors only consider the first candidate, mirror that here.
            break;
        }

        return $toolCalls;
    }

    /**
     * Concatenate the text parts of the first candidate without throwing when the response
     * also contains non-text parts (e.g. function calls).
     */
    private function extractText(GenerateContentResponse $result): string
    {
        $text = '';
        foreach ($result->candidates as $candidate) {
            foreach ($candidate->content->parts as $part) {
                if (null !== $part->text) {
                    $text .= $part->text;
                }
            }

            break;
        }

        return $text;
    }

    /**
     * Gemini expects a function response as a JSON object. Decode JSON object results so the
     * structure is preserved, otherwise wrap the raw content so it is still handed back to the model.
     *
     * @return array<string, mixed>
     */
    private function decodeToolResult(string $content): array
    {
        /** @var mixed $decoded */
        $decoded = \json_decode($content, true);
        if (!\is_array($decoded) || \array_is_list($decoded)) {
            return ['content' => $content];
        }

        $response = [];
        /** @var mixed $value */
        foreach ($decoded as $key => $value) {
            $response[(string) $key] = $value;
        }

        return $response;
    }

    /**
     * @param Content[] $messages
     */
    private function createStreamed(AIChatStreamedRequest $request, array $messages, GenerativeModelContract $model): AIChatResponse
    {
        $result = $model->streamGenerateContent(...$messages);

        $usageTracker = new StreamingUsageTracker(false);

        return new AIChatResponseStream(
            request: $request,
            messages: $this->createStreamedMessages($result->getIterator(), $usageTracker),
            usageTracker: $usageTracker,
        );
    }

    /**
     * @param \Iterator<int, GenerateContentResponse> $responses
     *
     * @return \Iterator<int, AIChatResponseMessage>
     */
    private function createStreamedMessages(\Iterator $responses, ?StreamingUsageTracker $usageTracker = null): \Iterator
    {
        try {
            $lastUsage = null;
            while ($responses->valid()) {
                $response = $responses->current();

                if ($usageTracker instanceof StreamingUsageTracker && null !== $response->usageMetadata) {
                    $lastUsage = new Usage(
                        $response->usageMetadata->promptTokenCount,
                        $response->usageMetadata->totalTokenCount - $response->usageMetadata->promptTokenCount,
                        $response->usageMetadata->totalTokenCount,
                    );
                }

                $toolCalls = $this->extractToolCalls($response);

                if ([] === $toolCalls) {
                    try {
                        $text = $response->text();
                    } catch (\ValueError $exception) {
                        throw new \RuntimeException(
                            message: \sprintf('Request blocked by safety settings: %s', $exception->getMessage()),
                            code: $exception->getCode(),
                            previous: $exception,
                        );
                    }
                } else {
                    $text = $this->extractText($response);
                }

                yield new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, $text, $toolCalls);

                $responses->next();
            }

            if ($usageTracker instanceof StreamingUsageTracker && $lastUsage instanceof Usage) {
                $usageTracker->updateUsage($lastUsage, true);
            }
        } catch (\Exception $e) {
            throw new \RuntimeException('Error processing stream response: ' . $e->getMessage(), 0, $e);
        }
    }

    public function supports(object $request): bool
    {
        return $request instanceof AIChatRequest;
    }

    public function supportsResponseFormat(ResponseFormatInterface $responseFormat): bool
    {
        return $responseFormat instanceof JsonResponseFormat
            || $responseFormat instanceof JsonSchemaResponseFormat;
    }
}
