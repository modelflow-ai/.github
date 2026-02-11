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

namespace ModelflowAi\AnthropicAdapter\Chat;

use ModelflowAi\Chat\ToolInfo\Parameter;
use ModelflowAi\Chat\ToolInfo\ToolInfo;

final class ToolFormatter
{
    /**
     * @return array{
     *     name: string,
     *     description: string,
     *     input_schema: array{
     *         type: string,
     *         properties: array<string, array<string, mixed>>,
     *         required?: string[],
     *     },
     * }
     */
    public static function formatTool(ToolInfo $tool): array
    {
        $parameters = [];
        foreach ($tool->parameters as $parameter) {
            $param = self::formatParameter($parameter);
            $parameters[$parameter->name] = $param;
        }

        $requiredParameters = [];
        foreach ($tool->requiredParameters as $requiredParameter) {
            $requiredParameters[] = $requiredParameter->name;
        }

        return [
            'name' => $tool->name,
            'description' => $tool->description,
            'input_schema' => [
                'type' => 'object',
                'properties' => $parameters,
                'required' => $requiredParameters,
            ],
        ];
    }

    /**
     * @param ToolInfo[] $tools
     *
     * @return array<array{
     *     name: string,
     *     description: string,
     *     input_schema: array{
     *         type: string,
     *         properties: array<string, array<string, mixed>>,
     *         required?: string[],
     *     },
     * }>
     */
    public static function formatTools(array $tools): array
    {
        return \array_map(
            self::formatTool(...),
            $tools,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private static function formatParameter(Parameter $parameter): array
    {
        $type = $parameter->nullable ? [$parameter->type, 'null'] : $parameter->type;

        $param = [
            'type' => $type,
            'description' => $parameter->description,
        ];

        if ('array' === $parameter->type) {
            if (null === $parameter->itemsOrProperties) {
                throw new \Exception('Array type parameter must have items description. Define a type or use the Parameter class for object.');
            }

            if (\is_string($parameter->itemsOrProperties)) {
                $param['items'] = [
                    'type' => $parameter->itemsOrProperties,
                ];
            } else {
                $properties = [];
                /** @var Parameter $property */
                foreach ($parameter->itemsOrProperties as $property) {
                    $properties[$property->name] = self::formatParameter($property);
                }

                $items = [
                    'type' => 'object',
                    'properties' => $properties,
                ];

                if ([] !== $parameter->required) {
                    $items['required'] = $parameter->required;
                }

                $param['items'] = $items;
            }
        }

        if ('object' === $parameter->type) {
            if (!\is_array($parameter->itemsOrProperties)) {
                throw new \Exception('Object type parameter must have properties description. You need to pass an array of Parameter.');
            }

            $properties = [];
            /** @var Parameter $item */
            foreach ($parameter->itemsOrProperties as $item) {
                $properties[$item->name] = self::formatParameter($item);
            }

            $param['properties'] = $properties;

            if ([] !== $parameter->required) {
                $param['required'] = $parameter->required;
            }
        }

        if ([] !== $parameter->enum) {
            $param['enum'] = $parameter->enum;
        }

        if (null !== $parameter->format) {
            $param['format'] = $parameter->format;
        }

        return $param;
    }
}
