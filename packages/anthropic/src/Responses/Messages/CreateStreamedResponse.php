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
 */
final readonly class CreateStreamedResponse
{
    private function __construct(
        public int $index,
        public string $id,
        public string $type,
        public string $role,
        public string $model,
        public ?string $stopSequence,
        public Usage $usage,
        public ?CreateStreamedResponseDelta $content,
        public ?string $stopReason,
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
     *     content: array{
     *         index?: int,
     *         type?: "text"|"text_delta"|"tool_use"|"input_json_delta",
     *         text?: string,
     *         id?: string,
     *         name?: string,
     *         input?: array<string, mixed>,
     *         partial_json?: string,
     *     },
     *     stop_reason: "end_turn"|"max_tokens"|"stop_sequence"|"tool_use"|null,
     * } $attributes
     */
    public static function from(int $index, array $attributes, MetaInformation $meta): self
    {
        return new self(
            $index,
            $attributes['id'],
            $attributes['type'],
            $attributes['role'],
            $attributes['model'],
            $attributes['stop_sequence'],
            UsageFactory::from($attributes['usage']),
            CreateStreamedResponseDelta::from($attributes['content']),
            $attributes['stop_reason'],
            $meta,
        );
    }
}
