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

namespace ModelflowAi\Chat\Response;

use ModelflowAi\Chat\ToolInfo\ToolTypeEnum;

readonly class AIChatToolCall
{
    /**
     * @param array<string, mixed> $arguments
     * @param string|null $signature Provider scoped signature guarding this tool call when it is replayed
     *                               in a follow-up request. Currently only Google Gemini emits one.
     */
    public function __construct(
        public ToolTypeEnum $type,
        public string $id,
        public string $name,
        public array $arguments,
        public ?string $signature = null,
    ) {
    }
}
