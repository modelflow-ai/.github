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

use ModelflowAi\Core\Response\AIChatToolCall;

readonly class ToolCallsPart extends MessagePart
{
    /**
     * @param AIChatToolCall[] $toolCalls
     */
    public static function create(
        array $toolCalls,
    ): self {
        return new self($toolCalls);
    }

    /**
     * @param AIChatToolCall[] $toolCalls
     */
    public function __construct(
        public array $toolCalls,
    ) {
        parent::__construct(MessagePartTypeEnum::TOOL_CALLS);
    }

    public function enhanceMessage(array $message): array
    {
        $message['content'] = '';
        $message['tool_calls'] = \array_map(
            fn (AIChatToolCall $tool) => [
                'id' => $tool->id,
                'type' => $tool->type->value,
                'function' => [
                    'name' => $tool->name,
                    'arguments' => (string) \json_encode($tool->arguments),
                ],
            ],
            $this->toolCalls,
        );

        return $message;
    }
}
