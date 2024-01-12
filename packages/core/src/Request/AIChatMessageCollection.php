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

use ModelflowAi\PromptTemplate\Chat\AIChatMessage;

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

    /**
     * @return array<array{
     *     role: "assistant"|"system"|"user",
     *     content: string,
     * }>
     */
    public function toArray(): array
    {
        return \array_map(
            fn (AIChatMessage $message) => [
                'role' => $message->role->value,
                'content' => $message->content,
            ],
            $this->getArrayCopy(),
        );
    }
}
