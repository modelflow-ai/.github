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

namespace ModelflowAi\Anthropic\Resources;

use ModelflowAi\Anthropic\Responses\Messages\CreateResponse;
use ModelflowAi\Anthropic\Responses\Messages\CreateStreamedResponse;

/**
 * @phpstan-type TextMessage array{type: "text", text: string}
 * @phpstan-type ImageMessage array{type: "image", source: array{type: "base64", media_type: string, data: string}}
 * @phpstan-type ToolUseMessage array{type: "tool_use", id: string, name: string, input: array<string, mixed>}
 * @phpstan-type ToolUseRequestMessage array{type: "tool_use", id: string, name: string, input: array<string, mixed>|\stdClass}
 * @phpstan-type ToolResultMessage array{type: "tool_result", tool_use_id: string, content: array<array{type: "text", text: string|null}>}
 * @phpstan-type MessageContent string|TextMessage|ImageMessage|ToolUseRequestMessage|ToolResultMessage|array<TextMessage|ImageMessage|ToolUseRequestMessage|ToolResultMessage>
 * @phpstan-type Message array{role: "system"|"assistant"|"user", content: MessageContent}
 * @phpstan-type Tool array{
 *     name: string,
 *     description: string,
 *     input_schema: array{
 *         type: string,
 *         properties: array<string, array<string, mixed>>|\stdClass,
 *         required?: string[],
 *     }
 * }
 * @phpstan-type OutputConfig array{
 *     format: array{
 *         type: "json_schema",
 *         schema: array<string, mixed>,
 *     }
 * }
 * @phpstan-type Thinking array{type: "adaptive", display?: "summarized"|"omitted"}
 *     |array{type: "disabled"}
 *     |array{type: "enabled", budget_tokens: int, display?: "summarized"|"omitted"}
 * @phpstan-type Parameters array{
 *     model: string,
 *     messages: Message[],
 *     tools?: Tool[],
 *     max_tokens: int,
 *     metadata?: array{user_id: string},
 *     stop_sequences?: string[],
 *     temperature?: float,
 *     top_k?: int,
 *     top_p?: float,
 *     thinking?: Thinking,
 *     output_config?: OutputConfig,
 * }
 */
interface MessagesInterface
{
    /**
     * @param Parameters $parameters
     */
    public function create(array $parameters): CreateResponse;

    /**
     * @param Parameters $parameters
     *
     * @return \Iterator<int, CreateStreamedResponse>
     */
    public function createStreamed(array $parameters): \Iterator;
}
