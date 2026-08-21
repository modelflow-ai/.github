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

namespace ModelflowAi\Anthropic\Responses\Messages;

/**
 * @phpstan-import-type TextMessage from \ModelflowAi\Anthropic\Resources\MessagesInterface
 * @phpstan-import-type ToolUseMessage from \ModelflowAi\Anthropic\Resources\MessagesInterface
 */
final readonly class CreateResponseContent
{
    public function __construct(
        public string $type,
        public ?string $text,
        public ?CreateResponseContentToolUse $toolUse = null,
    ) {
    }

    /**
     * @param TextMessage|ToolUseMessage $attributes
     */
    public static function from(array $attributes): self
    {
        if ('text' === $attributes['type']) {
            return new self(
                $attributes['type'],
                $attributes['text'],
            );
        }
        if ('tool_use' === $attributes['type']) {
            return new self(
                $attributes['type'],
                null,
                CreateResponseContentToolUse::from($attributes),
            );
        }

        // @phpstan-ignore-next-line
        throw new \InvalidArgumentException('Invalid type');
    }
}
