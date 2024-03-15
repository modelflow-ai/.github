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
        $reflection = new \ReflectionMethod($instance::class, $method);
        $params = $reflection->getParameters();

        $parameters = [];
        $requiredParameters = [];

        $docComment = $reflection->getDocComment() ?: '';

        foreach ($params as $param) {
            /** @var \ReflectionNamedType $reflectionType */
            $reflectionType = $param->getType();

            \preg_match(\sprintf('/\* @param [^\$]* \$%s (?<description>.*)/', $param->getName()), $docComment, $matches);

            $newParameter = new Parameter(
                $param->getName(),
                Types::mapPhpTypeToJsonSchemaType($reflectionType),
                $matches['description'] ?? '',
            );

            if ('array' === $newParameter->type) {
                $newParameter->itemsOrProperties = self::getArrayType($reflection->getDocComment() ?: '', $param->getName());
            }

            $parameters[] = $newParameter;
            if (!$param->isOptional()) {
                $requiredParameters[] = $newParameter;
            }
        }

        // Remove PHPDoc annotations and get only the description
        $functionDescription = \preg_replace('/\s*\* @.*/', '', $docComment);
        $functionDescription = \trim(\str_replace(['/**', '*/', '*'], '', $functionDescription ?? ''));

        return new ToolInfo($type, $name, $functionDescription, $parameters, $requiredParameters);
    }

    private static function getArrayType(string $doc, string $paramName): ?string
    {
        // Use a regex to find the parameter type
        $pattern = "/@param\s+([a-zA-Z0-9_|\\\[\]]+)\s+\\$" . $paramName . '/';
        if (\preg_match($pattern, $doc, $matches)) {
            // If the type is an array type (e.g., string[]), return the type without the brackets
            return \preg_replace('/\[\]$/', '', $matches[1]);
        }

        // If the parameter was not found, return null
        return null;
    }
}
