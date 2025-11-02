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

namespace ModelflowAi\Chat\Adapter\Fake;

use ModelflowAi\Chat\Adapter\AIChatAdapterInterface;
use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\Chat\Request\AIChatStreamedRequest;
use ModelflowAi\Chat\Response\AIChatResponse;
use ModelflowAi\Chat\Response\AIChatResponseInterface;
use ModelflowAi\Chat\Response\AIChatResponseMessage;
use ModelflowAi\Chat\Response\AIChatResponseStream;
use ModelflowAi\Chat\Response\StreamingUsageTracker;
use ModelflowAi\Chat\Response\Usage;
use Webmozart\Assert\Assert;

class FakeChatAdapter implements AIChatAdapterInterface
{
    /**
     * @var array<array{0: AIChatResponseMessage|AIChatResponseMessage[], 1: Usage|null}>
     */
    private array $messages = [];

    /**
     * @param AIChatResponseMessage|AIChatResponseMessage[] $message
     */
    public function addMessage(AIChatResponseMessage|array $message, ?Usage $usage = null): void
    {
        $this->messages[] = [$message, $usage];
    }

    public function handleRequest(AIChatRequest $request): AIChatResponseInterface
    {
        /** @var AIChatResponseMessage|AIChatResponseMessage[] $message */
        /** @var Usage|null $usage */
        [$message, $usage] = \array_shift($this->messages); // @phpstan-ignore-line
        Assert::notNull($message);

        if ($request instanceof AIChatStreamedRequest) {
            if (!\is_array($message)) {
                $message = [$message];
            }

            $usageTracker = new StreamingUsageTracker();
            if ($usage instanceof Usage) {
                $usageTracker->updateUsage($usage, true);
            }

            return new AIChatResponseStream($request, $this->stream($message), [], $usageTracker);
        }

        Assert::isInstanceOf($message, AIChatResponseMessage::class);

        return new AIChatResponse($request, $message, $usage ?? new Usage(0, 0, 0));
    }

    public function supports(object $request): bool
    {
        return $request instanceof AIChatRequest;
    }

    /**
     * @param AIChatResponseMessage[] $messages
     *
     * @return \Generator<int, AIChatResponseMessage>
     */
    public function stream(array $messages): \Generator
    {
        foreach ($messages as $message) {
            yield $message;

            \usleep(500000);
        }
    }
}
