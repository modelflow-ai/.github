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

namespace ModelflowAi\FireworksAiAdapter\Chat;

use ModelflowAi\Chat\ToolInfo\Parameter;
use ModelflowAi\Chat\ToolInfo\ToolInfo;

final class ToolFormatter
{
    /**
     * @return array{
     *     name: string,
     *     description: string,
     *     parameters: array{
     *         type: string,
     *         properties: array<string, mixed[]>|\stdClass,
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
                // a tool without parameters has to send an empty object, an empty array would be
                // encoded as [] and is rejected by the api
                'properties' => [] !== $parameters ? $parameters : new \stdClass(),
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
     *            properties: array<string, mixed[]>|\stdClass,
     *            required: string[],
     *        },
     *    },
     * }>
     */
    public static function formatTools(array $tools): array
    {
        return \array_map(
            static fn (ToolInfo $tool) => [
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
     *     type: string|string[],
     *     description: string,
     *     items?: array{
     *         type: string|string[],
     *         properties?: array<string, mixed>|\stdClass,
     *         required?: string[],
     *     },
     *     properties?: array<string, mixed>|\stdClass,
     *     required?: string[],
     *     enum?: mixed[],
     *     format?: string,
     * }
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
                    'properties' => [] !== $properties ? $properties : new \stdClass(),
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

            $param['properties'] = [] !== $properties ? $properties : new \stdClass();

            if ([] !== $parameter->required) {
                $param['required'] = $parameter->required;
            }
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
