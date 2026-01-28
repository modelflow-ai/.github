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

namespace ModelflowAi\Chat\Request;

use ModelflowAi\Chat\Request\Message\AIChatMessage;
use ModelflowAi\Chat\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Chat\Request\ResponseFormat\ResponseFormatInterface;

/**
 * @extends \ArrayObject<int, AIChatMessage>
 */
class AIChatMessageCollection extends \ArrayObject
{
    private ?ResponseFormatInterface $responseFormat = null;

    public function __construct(
        AIChatMessage ...$messages,
    ) {
        parent::__construct(\array_values($messages));
    }

    public function latest(): ?AIChatMessage
    {
        if (0 === $this->count()) {
            return null;
        }

        return $this->offsetGet($this->count() - 1);
    }

    /**
     * @return array<array{
     *     role: "assistant"|"system"|"user"|"tool",
     *     content: string,
     *     images?: string[],
     * }>
     */
    public function toArray(): array
    {
        return \array_map(
            static fn (AIChatMessage $message) => $message->toArray(),
            $this->getArrayCopy(),
        );
    }

    public function addResponseFormat(ResponseFormatInterface $responseFormat): void
    {
        if ($this->responseFormat instanceof ResponseFormatInterface) {
            throw new \RuntimeException('Response format already set.');
        }

        $this->responseFormat = $responseFormat;

        if (0 === $this->count()) {
            $this->exchangeArray([
                new AIChatMessage(AIChatMessageRoleEnum::SYSTEM, $responseFormat->asString()),
            ]);

            return;
        }

        $handled = false;
        $messages = [];

        /** @var AIChatMessage $message */
        foreach ($this as $message) {
            if (false === $handled && AIChatMessageRoleEnum::SYSTEM !== $message->role) {
                $messages[] = new AIChatMessage(AIChatMessageRoleEnum::SYSTEM, $responseFormat->asString());
                $handled = true;
            }

            $messages[] = $message;
        }

        $this->exchangeArray($messages);
    }
}
