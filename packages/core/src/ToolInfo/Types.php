<?php

namespace ModelflowAi\Core\ToolInfo;

/**
 * Inspired by https://github.com/theodo-group/LLPhant/blob/4825d36/src/Chat/FunctionInfo/TypeMapper.php.
 */
final class Types
{
    /**
     * @var array<string, string>
     */
    private const MAPPING = [
        'string' => 'string',
        'int' => 'integer',
        'float' => 'number',
        'bool' => 'boolean',
        'array' => 'array',
    ];

    public static function mapPhpTypeToJsonSchemaType(\ReflectionNamedType $reflectionType): string
    {
        $name = $reflectionType->getName();

        if (! isset(self::MAPPING[$name])) {
            throw new \InvalidArgumentException("Unsupported type: {$name}");
        }

        return self::MAPPING[$name];
    }
}
