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

namespace ModelflowAi\Core\Response;

use ModelflowAi\Core\ToolInfo\ToolTypeEnum;

readonly class AIChatToolCall
{
    /**
     * @param array<string, mixed> $arguments
     */
    public function __construct(
        public ToolTypeEnum $type,
        public string $id,
        public string $name,
        public array $arguments,
    ) {
    }
}
