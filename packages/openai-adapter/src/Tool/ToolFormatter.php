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

namespace ModelflowAi\OpenaiAdapter\Tool;

use ModelflowAi\Core\ToolInfo\Parameter;
use ModelflowAi\Core\ToolInfo\ToolInfo;

final class ToolFormatter
{
    /**
     * @return array{
     *     name: string,
     *     description: string,
     *     parameters: array{
     *         type: string,
     *         properties: array<string, mixed[]>,
     *         required: string[],
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
            'parameters' => [
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
     *     type: string,
     *     function: array{
     *        name: string,
     *        description: string,
     *        parameters: array{
     *            type: string,
     *            properties: array<string, mixed[]>,
     *            required: string[],
     *        },
     *    },
     * }>
     */
    public static function formatTools(array $tools): array
    {
        return \array_map(
            fn (ToolInfo $tool) => [
                'type' => $tool->type->value,
                'function' => self::formatTool($tool),
            ],
            $tools,
        );
    }

    /**
     * @throws \Exception
     *
     * @return array{
     *     type: string,
     *     description: string,
     *     items?: array{
     *         type: string,
     *         properties?: array<string, array{
     *             type: string, description: string,
     *         }>
     *     },
     *     properties?: array<string, array{
     *         type: string,
     *          description: string,
     *     }>,
     *     enum?: mixed[],
     *     format?: string,
     * }
     */
    protected static function formatParameter(Parameter $parameter): array
    {
        $param = [
            'type' => $parameter->type,
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
                    $properties[$property->name] = [
                        'type' => $property->type,
                        'description' => $property->description,
                    ];
                }

                $param['items'] = [
                    'type' => 'object',
                    'properties' => $properties,
                ];
            }
        }

        if ('object' === $parameter->type) {
            if (!\is_array($parameter->itemsOrProperties)) {
                throw new \Exception('Object type parameter must have properties description. You need to pass an array of Parameter.');
            }

            $properties = [];
            /** @var Parameter $item */
            foreach ($parameter->itemsOrProperties as $item) {
                $properties[$item->name] = [
                    'type' => $item->type,
                    'description' => $item->description,
                ];
            }

            $param['properties'] = $properties;
        }

        if ($parameter->enum) {
            $param['enum'] = $parameter->enum;
        }

        if ($parameter->format) {
            $param['format'] = $parameter->format;
        }

        return $param;
    }
}
