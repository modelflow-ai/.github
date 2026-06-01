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

namespace ModelflowAi\GoogleGeminiAdapter\Chat;

use Gemini\Data\DataFormat;
use Gemini\Data\FunctionDeclaration;
use Gemini\Data\Schema;
use Gemini\Data\Tool;
use Gemini\Enums\DataType;
use ModelflowAi\Chat\ToolInfo\Parameter;
use ModelflowAi\Chat\ToolInfo\ToolInfo;

final class ToolFormatter
{
    /**
     * Build a single Google Gemini {@see Tool} that exposes all given tools as function declarations.
     *
     * @param ToolInfo[] $tools
     */
    public static function formatTools(array $tools): Tool
    {
        return new Tool(
            functionDeclarations: \array_map(
                self::formatTool(...),
                \array_values($tools),
            ),
        );
    }

    public static function formatTool(ToolInfo $tool): FunctionDeclaration
    {
        $properties = [];
        foreach ($tool->parameters as $parameter) {
            $properties[$parameter->name] = self::formatParameter($parameter);
        }

        $required = [];
        foreach ($tool->requiredParameters as $requiredParameter) {
            $required[] = $requiredParameter->name;
        }

        return new FunctionDeclaration(
            name: $tool->name,
            description: $tool->description,
            parameters: new Schema(
                type: DataType::OBJECT,
                properties: [] !== $properties ? $properties : null,
                required: [] !== $required ? $required : null,
            ),
        );
    }

    private static function formatParameter(Parameter $parameter): Schema
    {
        $type = self::convertType($parameter->type);

        $items = null;
        $properties = null;
        $required = null;

        if (DataType::ARRAY === $type) {
            if (null === $parameter->itemsOrProperties) {
                throw new \InvalidArgumentException('Array type parameter must have items description. Define a type or use the Parameter class for object.');
            }

            if (\is_string($parameter->itemsOrProperties)) {
                $items = new Schema(type: self::convertType($parameter->itemsOrProperties));
            } else {
                $itemProperties = [];
                foreach ($parameter->itemsOrProperties as $property) {
                    $itemProperties[$property->name] = self::formatParameter($property);
                }

                $items = new Schema(
                    type: DataType::OBJECT,
                    properties: [] !== $itemProperties ? $itemProperties : null,
                    required: [] !== $parameter->required ? $parameter->required : null,
                );
            }
        }

        if (DataType::OBJECT === $type) {
            if (!\is_array($parameter->itemsOrProperties)) {
                throw new \InvalidArgumentException('Object type parameter must have properties description. You need to pass an array of Parameter.');
            }

            $objectProperties = [];
            foreach ($parameter->itemsOrProperties as $item) {
                $objectProperties[$item->name] = self::formatParameter($item);
            }

            $properties = [] !== $objectProperties ? $objectProperties : null;
            $required = [] !== $parameter->required ? $parameter->required : null;
        }

        return new Schema(
            type: $type,
            format: self::convertFormat($parameter->format),
            description: '' !== $parameter->description ? $parameter->description : null,
            nullable: $parameter->nullable ? true : null,
            enum: self::convertEnum($parameter->enum),
            properties: $properties,
            required: $required,
            items: $items,
        );
    }

    private static function convertType(string $type): DataType
    {
        return match ($type) {
            'string' => DataType::STRING,
            'integer' => DataType::INTEGER,
            'number' => DataType::NUMBER,
            'boolean' => DataType::BOOLEAN,
            'array' => DataType::ARRAY,
            'object' => DataType::OBJECT,
            default => DataType::STRING,
        };
    }

    /**
     * Gemini only accepts a fixed set of formats. Drop anything it does not understand.
     */
    private static function convertFormat(?string $format): ?DataFormat
    {
        if (null === $format) {
            return null;
        }

        return DataFormat::tryFrom($format);
    }

    /**
     * @param mixed[] $enum
     *
     * @return array<string>|null
     */
    private static function convertEnum(array $enum): ?array
    {
        if ([] === $enum) {
            return null;
        }

        $values = [];
        foreach ($enum as $value) {
            if (\is_scalar($value)) {
                $values[] = (string) $value;
            }
        }

        return [] !== $values ? $values : null;
    }
}
