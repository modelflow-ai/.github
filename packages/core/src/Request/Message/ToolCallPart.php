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

readonly class ToolCallPart extends MessagePart
{
    public static function create(
        string $toolCallId,
        string $toolName,
        string $content,
    ): self {
        return new self(
            $toolCallId,
            $toolName,
            $content,
        );
    }

    public function __construct(
        public string $toolCallId,
        public string $toolName,
        public string $content,
    ) {
        parent::__construct(MessagePartTypeEnum::TOOL_CALL);
    }

    public function enhanceMessage(array $message): array
    {
        $message['tool_call_id'] = $this->toolCallId;
        $message['name'] = $this->toolName;
        $message['content'] = $this->content;

        return $message;
    }
}
