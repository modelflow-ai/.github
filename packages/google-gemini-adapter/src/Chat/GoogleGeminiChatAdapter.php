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
use Gemini\Enums\MimeType;
use Gemini\Enums\Role;
use Gemini\Resources\GenerativeModel;
use Gemini\Responses\GenerativeModel\GenerateContentResponse;
use ModelflowAi\Chat\Adapter\AIChatAdapterInterface;
use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\Chat\Request\AIChatStreamedRequest;
use ModelflowAi\Chat\Request\Message\AIChatMessage;
use ModelflowAi\Chat\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Chat\Request\Message\ImageBase64Part;
use ModelflowAi\Chat\Request\Message\TextPart;
use ModelflowAi\Chat\Response\AIChatResponse;
use ModelflowAi\Chat\Response\AIChatResponseMessage;
use ModelflowAi\Chat\Response\AIChatResponseStream;
use ModelflowAi\Chat\Response\Usage;
use Webmozart\Assert\Assert;

final readonly class GoogleGeminiChatAdapter implements AIChatAdapterInterface
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

        $config = new GenerationConfig();
        if ($request->getOption('temperature')) {
            $temperature = $request->getOption('temperature');
            Assert::float($temperature);

            $config = new GenerationConfig(temperature: $temperature);
        }

        /** @var GenerativeModel $model */
        $model = $this->client->generativeModel($this->model);
        if ($model instanceof GenerativeModel) {
            // TODO: Remove this workaround once the mock class implements withGenerationConfig
            // This is a temporary workaround because the mock class doesn't implement withGenerationConfig
            $model = $model->withGenerationConfig($config);
        }

        if ($request instanceof AIChatStreamedRequest) {
            return $this->createStreamed($request, $messages, $model);
        }

        return $this->create($request, $messages, $model);
    }

    /**
     * @param Content[] $messages
     */
    protected function create(AIChatRequest $request, array $messages, GenerativeModelContract $model): AIChatResponse
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
    protected function createStreamed(AIChatStreamedRequest $request, array $messages, GenerativeModelContract $model): AIChatResponse
    {
        $result = $model->streamGenerateContent(...$messages);

        return new AIChatResponseStream(
            $request,
            $this->createStreamedMessages($result->getIterator()),
        );
    }

    /**
     * @param \Iterator<int, GenerateContentResponse> $responses
     *
     * @return \Iterator<int, AIChatResponseMessage>
     */
    protected function createStreamedMessages(\Iterator $responses): \Iterator
    {
        try {
            while ($responses->valid()) {
                $response = $responses->current();
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
        } catch (\Exception $e) {
            throw new \RuntimeException('Error processing stream response: ' . $e->getMessage(), 0, $e);
        }
    }

    public function supports(object $request): bool
    {
        return $request instanceof AIChatRequest;
    }
}
