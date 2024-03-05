<?php

namespace ModelflowAi\Core\Tool;

/**
 * Inspired by https://github.com/theodo-group/LLPhant/blob/4825d36/src/Chat/FunctionInfo/FunctionInfo.php.
 */
final readonly class ToolInfo
{
    public function __construct(
        public ToolTypeEnum $type,
        public string $name,
        public string $description,
        public array $parameters,
        public array $requiredParameters = [],
    ) {
    }
}
