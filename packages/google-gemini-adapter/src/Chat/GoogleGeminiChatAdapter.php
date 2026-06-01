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
use Gemini\Data\GenerationConfig;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;
use Gemini\Enums\MimeType;
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
use ModelflowAi\Chat\Request\ResponseFormat\JsonResponseFormat;
use ModelflowAi\Chat\Request\ResponseFormat\JsonSchemaResponseFormat;
use ModelflowAi\Chat\Request\ResponseFormat\ResponseFormatInterface;
use ModelflowAi\Chat\Request\ResponseFormat\SupportsResponseFormatInterface;
use ModelflowAi\Chat\Response\AIChatResponse;
use ModelflowAi\Chat\Response\AIChatResponseMessage;
use ModelflowAi\Chat\Response\AIChatResponseStream;
use ModelflowAi\Chat\Response\StreamingUsageTracker;
use ModelflowAi\Chat\Response\Usage;
use Webmozart\Assert\Assert;

final readonly class GoogleGeminiChatAdapter implements AIChatAdapterInterface, SupportsResponseFormatInterface
{
    public const EXPECTED_ROLES = [
        AIChatMessageRoleEnum::SYSTEM,
        AIChatMessageRoleEnum::ASSISTANT,
        AIChatMessageRoleEnum::USER,
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

            $message = [];

            foreach ($aiMessage->parts as $part) {
                if ($part instanceof TextPart) {
                    $message[] = $part->text;
                } elseif ($part instanceof ImageBase64Part) {
                    $message[] = new Blob(MimeType::from($part->mimeType), $part->content);
                } else {
                    throw new \Exception('Not supported message part type.');
                }
            }

            $geminiRole = match ($aiMessage->role) {
                AIChatMessageRoleEnum::USER => Role::USER,
                default => Role::MODEL,
            };

            $messages[] = Content::parse($message, $geminiRole);
        }

        $responseFormat = $request->getResponseFormat();
        $config = $this->buildGenerationConfig($request, $responseFormat);

        $model = $this->client->generativeModel($this->model);
        $model = $model->withGenerationConfig($config);

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

        try {
            $text = $result->text();
        } catch (\ValueError $exception) {
            throw new \RuntimeException(
                message: \sprintf('Request blocked by safety settings: %s', $exception->getMessage()),
                code: $exception->getCode(),
                previous: $exception,
            );
        }

        if (\str_starts_with($text, '```json') && \str_ends_with($text, '```')) {
            $text = \substr($text, 7, -3);
        }

        return new AIChatResponse(
            $request,
            new AIChatResponseMessage(
                AIChatMessageRoleEnum::ASSISTANT,
                $text,
            ),
            new Usage(
                $result->usageMetadata->promptTokenCount,
                $result->usageMetadata->totalTokenCount - $result->usageMetadata->promptTokenCount,
                $result->usageMetadata->totalTokenCount,
            ),
        );
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

                try {
                    $text = $response->text();
                } catch (\ValueError $exception) {
                    throw new \RuntimeException(
                        message: \sprintf('Request blocked by safety settings: %s', $exception->getMessage()),
                        code: $exception->getCode(),
                        previous: $exception,
                    );
                }

                yield new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, $text);

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
