<?php

namespace ModelflowAi\Core\ToolInfo;

/**
 * Inspired by https://github.com/theodo-group/LLPhant/blob/4825d36/src/Chat/FunctionInfo/FunctionBuilder.php.
 */
class ToolInfoBuilder
{
    public static function buildToolInfo(
        object $instance,
        string $method,
        string $name,
        ToolTypeEnum $type = ToolTypeEnum::FUNCTION,
    ): ToolInfo {
        $reflection = new \ReflectionMethod(get_class($instance), $method);
        $params = $reflection->getParameters();

        $parameters = [];
        $requiredParameters = [];

        foreach ($params as $param) {
            /** @var \ReflectionNamedType $reflectionType */
            $reflectionType = $param->getType();

            $newParameter = new Parameter($param->getName(), Types::mapPhpTypeToJsonSchemaType($reflectionType), '');

            if ($newParameter->type === 'array') {
                $newParameter->itemsOrProperties = self::getArrayType($reflection->getDocComment() ?: '', $param->getName());
            }

            $parameters[] = $newParameter;
            if (! $param->isOptional()) {
                $requiredParameters[] = $newParameter;
            }
        }

        $docComment = $reflection->getDocComment() ?: '';

        // Remove PHPDoc annotations and get only the description
        $functionDescription = preg_replace('/\s*\* @.*/', '', $docComment);
        $functionDescription = trim(str_replace(['/**', '*/', '*'], '', $functionDescription ?? ''));

        return new ToolInfo($type, $name, $functionDescription, $parameters, $requiredParameters);
    }

    private static function getArrayType(string $doc, string $paramName): ?string
    {
        // Use a regex to find the parameter type
        $pattern = "/@param\s+([a-zA-Z0-9_|\\\[\]]+)\s+\\$".$paramName.'/';
        if (preg_match($pattern, $doc, $matches)) {
            // If the type is an array type (e.g., string[]), return the type without the brackets
            return preg_replace('/\[\]$/', '', $matches[1]);
        }

        // If the parameter was not found, return null
        return null;
    }
}
