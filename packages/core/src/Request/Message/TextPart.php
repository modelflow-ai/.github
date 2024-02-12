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

readonly class TextPart extends MessagePart
{
    public static function create(
        string $text,
    ): self {
        return new self($text);
    }

    public function __construct(
        public string $text,
    ) {
        parent::__construct(MessagePartTypeEnum::TEXT);
    }

    public function enhanceMessage(array $message): array
    {
        $message['content'] .= $this->text;

        return $message;
    }
}
