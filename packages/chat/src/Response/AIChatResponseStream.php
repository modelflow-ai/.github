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

namespace ModelflowAi\Chat\Response;

use ModelflowAi\Chat\Request\AIChatStreamedRequest;
use ModelflowAi\Chat\Request\Message\AIChatMessageRoleEnum;

readonly class AIChatResponseStream extends AIChatResponse implements AIChatResponseStreamInterface
{
    private AIChatResponseStreamMessageBuilder $messageBuilder;

    /**
     * @param \Iterator<int, AIChatResponseMessage> $messages
     * @param StreamingUsageTracker|null $usageTracker Optional tracker for streaming usage updates
     */
    public function __construct(
        private AIChatStreamedRequest $request,
        private \Iterator $messages,
        private array $metadata = [],
        private StreamingUsageTracker $usageTracker = new StreamingUsageTracker(),
    ) {
        parent::__construct(
            $request,
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, ''),
            null,
            $this->metadata,
        );

        $this->messageBuilder = new AIChatResponseStreamMessageBuilder();
    }

    public function getRequest(): AIChatStreamedRequest
    {
        return $this->request;
    }

    public function getMessage(): AIChatResponseMessage
    {
        return $this->messageBuilder->getMessage();
    }

    public function getMessageStream(): \Iterator
    {
        foreach ($this->messages as $message) {
            $this->messageBuilder->add($message);

            yield $message;
        }
    }

    public function registerUsageCallback(UsageCallbackInterface $callback): void
    {
        $this->usageTracker->registerCallback($callback);
    }

    public function getUsage(): ?Usage
    {
        return $this->usageTracker->getUsage();
    }

    /**
     * Internal method for adapters to update usage during streaming.
     *
     * @internal
     */
    public function updateUsage(Usage $usage, bool $isFinal = false): void
    {
        $this->usageTracker->updateUsage($usage, $isFinal);
    }
}
