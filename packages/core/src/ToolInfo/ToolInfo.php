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

namespace ModelflowAi\Core\ToolInfo;

/**
 * Inspired by https://github.com/theodo-group/LLPhant/blob/4825d36/src/Chat/FunctionInfo/FunctionInfo.php.
 */
final readonly class ToolInfo
{
    /**
     * @param Parameter[] $parameters
     * @param Parameter[] $requiredParameters
     */
    public function __construct(
        public ToolTypeEnum $type,
        public string $name,
        public string $description,
        public array $parameters,
        public array $requiredParameters = [],
    ) {
    }
}
