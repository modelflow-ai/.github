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

use ModelflowAi\Anthropic\Responses\UsageFactory;
use ModelflowAi\ApiClient\Responses\MetaInformation;
use ModelflowAi\ApiClient\Responses\Usage;

/**
 * @phpstan-import-type TextMessage from \ModelflowAi\Anthropic\Resources\MessagesInterface
 * @phpstan-import-type ToolUseMessage from \ModelflowAi\Anthropic\Resources\MessagesInterface
 */
final readonly class CreateResponse
{
    /**
     * @param CreateResponseContent[] $content
     */
    private function __construct(
        public string $id,
        public string $type,
        public string $role,
        public string $model,
        public ?string $stopSequence,
        public Usage $usage,
        public array $content,
        public string $stopReason,
        public MetaInformation $meta,
    ) {
    }

    /**
     * @param array{
     *     id: string,
     *     type: "message",
     *     role: "assistant",
     *     model: string,
     *     stop_sequence: string|null,
     *     usage: array{
     *         input_tokens: int,
     *         output_tokens: int,
     *     },
     *     content: array<TextMessage|ToolUseMessage>,
     *     stop_reason: "end_turn"|"max_tokens"|"stop_sequence"|"tool_use",
     * } $attributes
     */
    public static function from(array $attributes, MetaInformation $meta): self
    {
        $content = \array_map(static fn (array $result): CreateResponseContent => CreateResponseContent::from(
            $result,
        ), $attributes['content']);

        return new self(
            $attributes['id'],
            $attributes['type'],
            $attributes['role'],
            $attributes['model'],
            $attributes['stop_sequence'],
            UsageFactory::from($attributes['usage']),
            $content,
            $attributes['stop_reason'],
            $meta,
        );
    }
}
