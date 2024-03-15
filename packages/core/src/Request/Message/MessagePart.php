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

namespace ModelflowAi\Core\Request\Message;

abstract readonly class MessagePart
{
    public function __construct(
        public MessagePartTypeEnum $type,
    ) {
    }

    /**
     * @param array{
     *     role: "assistant"|"system"|"user"|"tool",
     *     content: string,
     *     tool_call_id?: string,
     *     name?: string,
     *     images?: string[],
     *     tool_calls?: array<array{
     *         id: string,
     *         type: "function",
     *         function: array{
     *             name: string,
     *             arguments: string
     *        },
     *     }>,
     * } $message
     *
     * @return array{
     *     role: "assistant"|"system"|"user"|"tool",
     *     content: string,
     *     tool_call_id?: string,
     *     name?: string,
     *     images?: string[],
     *     tool_calls?: array<array{
     *         id: string,
     *         type: "function",
     *         function: array{
     *             name: string,
     *             arguments: string
     *         },
     *     }>,
     * }
     */
    abstract public function enhanceMessage(array $message): array;
}
