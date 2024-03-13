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
 * Inspired by https://github.com/theodo-group/LLPhant/blob/4825d36/src/Chat/FunctionInfo/Parameter.php.
 */
class Parameter
{
    /**
     * @param mixed[] $enum
     * @param mixed[]|string|null $itemsOrProperties
     */
    public function __construct(
        public string $name,
        public string $type,
        public string $description,
        public array $enum = [],
        public ?string $format = null,
        public array|string|null $itemsOrProperties = null,
    ) {
    }
}
