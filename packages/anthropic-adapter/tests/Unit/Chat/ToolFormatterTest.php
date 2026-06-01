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

namespace ModelflowAi\AnthropicAdapter\Tests\Unit\Chat;

use ModelflowAi\AnthropicAdapter\Chat\ToolFormatter;
use ModelflowAi\Chat\ToolInfo\Parameter;
use ModelflowAi\Chat\ToolInfo\ToolInfo;
use ModelflowAi\Chat\ToolInfo\ToolInfoBuilder;
use ModelflowAi\Chat\ToolInfo\ToolTypeEnum;
use PHPUnit\Framework\TestCase;

class ToolFormatterTest extends TestCase
{
    public function testFormatTool(): void
    {
        $tool = ToolInfoBuilder::buildToolInfo($this, 'toolMethod1', 'test');

        $this->assertSame([
            'name' => 'test',
            'description' => 'This is a description.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'required' => [
                        'type' => 'string',
                        'description' => 'this is a required parameter',
                    ],
                    'optional' => [
                        'type' => 'string',
                        'description' => 'this is an optional parameter',
                    ],
                ],
                'required' => [
                    'required',
                ],
            ],
        ], ToolFormatter::formatTool($tool));
    }

    public function testFormatTools(): void
    {
        $tool1 = ToolInfoBuilder::buildToolInfo($this, 'toolMethod1', 'test');
        $tool2 = ToolInfoBuilder::buildToolInfo($this, 'toolMethod2', 'test');

        $this->assertSame(
            [
                [
                    'name' => 'test',
                    'description' => 'This is a description.',
                    'input_schema' => [
                        'type' => 'object',
                        'properties' => [
                            'required' => [
                                'type' => 'string',
                                'description' => 'this is a required parameter',
                            ],
                            'optional' => [
                                'type' => 'string',
                                'description' => 'this is an optional parameter',
                            ],
                        ],
                        'required' => [
                            'required',
                        ],
                    ],
                ],
                [
                    'name' => 'test',
                    'description' => '',
                    'input_schema' => [
                        'type' => 'object',
                        'properties' => [
                            'test' => [
                                'type' => 'string',
                                'description' => '',
                            ],
                        ],
                        'required' => [
                            'test',
                        ],
                    ],
                ],
            ],
            ToolFormatter::formatTools([
                $tool1,
                $tool2,
            ]),
        );
    }

    /**
     * This is a description.
     *
     * @param string $required this is a required parameter
     * @param string $optional this is an optional parameter
     */
    public function toolMethod1(string $required, string $optional = ''): string
    {
        return $required . $optional;
    }

    public function toolMethod2(string $test): void
    {
    }

    public function testFormatToolWithNestedObject(): void
    {
        $nestedProperties = [
            new Parameter('id', 'integer', 'The ID'),
            new Parameter('name', 'string', 'The name'),
            new Parameter('optional', 'string', 'Optional field'),
        ];

        $objectParam = new Parameter(
            name: 'user',
            type: 'object',
            description: 'User object',
            enum: [],
            format: null,
            itemsOrProperties: $nestedProperties,
        );

        $tool = new ToolInfo(
            type: ToolTypeEnum::FUNCTION,
            name: 'create_user',
            description: 'Create a user',
            parameters: [$objectParam],
            requiredParameters: [$objectParam],
        );

        $formatted = ToolFormatter::formatTool($tool);

        $this->assertSame([
            'name' => 'create_user',
            'description' => 'Create a user',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'user' => [
                        'type' => 'object',
                        'description' => 'User object',
                        'properties' => [
                            'id' => [
                                'type' => 'integer',
                                'description' => 'The ID',
                            ],
                            'name' => [
                                'type' => 'string',
                                'description' => 'The name',
                            ],
                            'optional' => [
                                'type' => 'string',
                                'description' => 'Optional field',
                            ],
                        ],
                    ],
                ],
                'required' => ['user'],
            ],
        ], $formatted);
    }

    public function testFormatToolWithArrayOfObjects(): void
    {
        $nestedProperties = [
            new Parameter('id', 'integer', 'Item ID'),
            new Parameter('name', 'string', 'Item name'),
        ];

        $arrayParam = new Parameter(
            name: 'items',
            type: 'array',
            description: 'List of items',
            enum: [],
            format: null,
            itemsOrProperties: $nestedProperties,
        );

        $tool = new ToolInfo(
            type: ToolTypeEnum::FUNCTION,
            name: 'process_items',
            description: 'Process items',
            parameters: [$arrayParam],
            requiredParameters: [$arrayParam],
        );

        $formatted = ToolFormatter::formatTool($tool);

        $this->assertSame([
            'name' => 'process_items',
            'description' => 'Process items',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'items' => [
                        'type' => 'array',
                        'description' => 'List of items',
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'id' => [
                                    'type' => 'integer',
                                    'description' => 'Item ID',
                                ],
                                'name' => [
                                    'type' => 'string',
                                    'description' => 'Item name',
                                ],
                            ],
                        ],
                    ],
                ],
                'required' => ['items'],
            ],
        ], $formatted);
    }

    public function testFormatToolWithOptionalParameter(): void
    {
        $nestedProperties = [
            new Parameter('id', 'integer', 'The ID'),
        ];

        $objectParam = new Parameter(
            name: 'data',
            type: 'object',
            description: 'Data object',
            enum: [],
            format: null,
            itemsOrProperties: $nestedProperties,
        );

        $tool = new ToolInfo(
            type: ToolTypeEnum::FUNCTION,
            name: 'test',
            description: 'Test',
            parameters: [$objectParam],
            requiredParameters: [],
        );

        $formatted = ToolFormatter::formatTool($tool);

        $this->assertSame([], $formatted['input_schema']['required'] ?? []);
    }
}
