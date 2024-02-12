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

namespace ModelflowAi\Core\Request;

use ModelflowAi\Core\Request\Message\AIChatMessage;

/**
 * @extends \ArrayObject<int, AIChatMessage>
 */
class AIChatMessageCollection extends \ArrayObject
{
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
     *     role: "assistant"|"system"|"user",
     *     content: string,
     *     images?: string[],
     * }>
     */
    public function toArray(): array
    {
        return \array_map(
            fn (AIChatMessage $message) => $message->toArray(),
            $this->getArrayCopy(),
        );
    }
}
