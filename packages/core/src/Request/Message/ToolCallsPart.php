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
     * @param AIChatToolCall[] $tools
     */
    public static function create(
        array $tools,
    ): self {
        return new self($tools);
    }

    /**
     * @param AIChatToolCall[] $tools
     */
    public function __construct(
        public array $tools,
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
                    'arguments' => \json_encode($tool->arguments),
                ],
            ],
            $this->tools,
        );

        return $message;
    }
}
