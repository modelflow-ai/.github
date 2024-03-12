<?php

namespace ModelflowAi\Core\ToolInfo;

/**
 * Inspired by https://github.com/theodo-group/LLPhant/blob/4825d36/src/Chat/FunctionInfo/Parameter.php.
 */
class Parameter
{
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
