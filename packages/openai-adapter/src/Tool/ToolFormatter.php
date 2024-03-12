<?php

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
     *
     * @throws \Exception
     */
    public static function formatParameter(Parameter $parameter): array
    {
        $param = [
            'type' => $parameter->type,
            'description' => $parameter->description,
        ];

        if ($parameter->type === 'array') {
            if ($parameter->itemsOrProperties === null) {
                throw new \Exception('Array type parameter must have items description. Define a type or use the Parameter class for object.');
            }

            if (is_string($parameter->itemsOrProperties)) {
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

        if ($parameter->type === 'object') {
            if (! is_array($parameter->itemsOrProperties)) {
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

    /**
     * @param ToolInfo[] $tools
     *
     * @return mixed[]
     */
    public static function formatTools(array $tools): array
    {
        return \array_map(
            fn (ToolInfo $tool) => [
                'type' => $tool->type->value,
                'function' => self::formatTool($tool),
            ],
            $tools
        );
    }
}
