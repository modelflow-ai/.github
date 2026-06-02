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

final readonly class CreateStreamedResponseDelta
{
    /**
     * @param array<string, mixed>|null $input
     */
    private function __construct(
        public int $index,
        public string $type,
        public ?string $text = null,
        public ?string $id = null,
        public ?string $name = null,
        public ?array $input = null,
        public ?string $partialJson = null,
    ) {
    }

    /**
     * Anthropic streams several content block shapes:
     * - text:             {index, type: "text", text: ""}              (content_block_start)
     * - text_delta:       {index, type: "text_delta", text: "..."}     (content_block_delta)
     * - tool_use:         {index, type: "tool_use", id, name, input}   (content_block_start)
     * - input_json_delta: {index, type: "input_json_delta", partial_json: "..."} (content_block_delta)
     *
     * @param array{
     *     index?: int,
     *     type?: "text"|"text_delta"|"tool_use"|"input_json_delta",
     *     text?: string,
     *     id?: string,
     *     name?: string,
     *     input?: array<string, mixed>,
     *     partial_json?: string,
     * } $attributes
     */
    public static function from(array $attributes): ?self
    {
        if (\in_array(null, [$attributes['index'] ?? null, $attributes['type'] ?? null], true)) {
            return null;
        }

        return new self(
            $attributes['index'],
            $attributes['type'],
            $attributes['text'] ?? null,
            $attributes['id'] ?? null,
            $attributes['name'] ?? null,
            $attributes['input'] ?? null,
            $attributes['partial_json'] ?? null,
        );
    }
}
