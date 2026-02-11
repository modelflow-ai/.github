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

namespace ModelflowAi\Chat\Tests\Unit\ToolInfo;

use ModelflowAi\Chat\ToolInfo\Parameter;
use ModelflowAi\Chat\ToolInfo\ToolInfo;
use ModelflowAi\Chat\ToolInfo\ToolTypeEnum;
use PHPUnit\Framework\TestCase;

class ToolInfoTest extends TestCase
{
    public function testType(): void
    {
        $message = new ToolInfo(ToolTypeEnum::FUNCTION, 'name', 'description', [new Parameter('name', 'string', 'Test description')], []);

        $this->assertSame(ToolTypeEnum::FUNCTION, $message->type);
    }

    public function testName(): void
    {
        $message = new ToolInfo(ToolTypeEnum::FUNCTION, 'name', 'description', [new Parameter('name', 'string', 'Test description')], []);

        $this->assertSame('name', $message->name);
    }

    public function testDescription(): void
    {
        $message = new ToolInfo(ToolTypeEnum::FUNCTION, 'name', 'description', [new Parameter('name', 'string', 'Test description')], []);

        $this->assertSame('description', $message->description);
    }

    public function testParameters(): void
    {
        $parameters = [
            new Parameter('name1', 'string', 'Test description'),
            new Parameter('name2', 'string', 'Test description'),
        ];

        $message = new ToolInfo(ToolTypeEnum::FUNCTION, 'name', 'description', $parameters, [$parameters[0]]);

        $this->assertSame($parameters, $message->parameters);
    }

    public function testRequiredParameters(): void
    {
        $parameters = [
            new Parameter('name1', 'string', 'Test description'),
            new Parameter('name2', 'string', 'Test description'),
        ];

        $message = new ToolInfo(ToolTypeEnum::FUNCTION, 'name', 'description', $parameters, [$parameters[0]]);

        $this->assertSame([$parameters[0]], $message->requiredParameters);
    }

    public function testToArray(): void
    {
        $parameters = [
            new Parameter('name1', 'string', 'Test description'),
            new Parameter('name2', 'string', 'Test description'),
        ];

        $message = new ToolInfo(ToolTypeEnum::FUNCTION, 'name', 'description', $parameters, [$parameters[0]]);

        $this->assertSame([
            'type' => ToolTypeEnum::FUNCTION->value,
            'name' => 'name',
            'description' => 'description',
            'parameters' => [
                [
                    'name' => 'name1',
                    'type' => 'string',
                    'description' => 'Test description',
                    'enum' => [],
                    'format' => null,
                    'itemsOrProperties' => null,
                    'nullable' => false,
                    'required' => [],
                ],
                [
                    'name' => 'name2',
                    'type' => 'string',
                    'description' => 'Test description',
                    'enum' => [],
                    'format' => null,
                    'itemsOrProperties' => null,
                    'nullable' => false,
                    'required' => [],
                ],
            ],
            'requiredParameters' => [
                [
                    'name' => 'name1',
                    'type' => 'string',
                    'description' => 'Test description',
                    'enum' => [],
                    'format' => null,
                    'itemsOrProperties' => null,
                    'nullable' => false,
                    'required' => [],
                ],
            ],
        ], $message->toArray());
    }

    public function testFromJsonSchemaOpenAiFormat(): void
    {
        $toolInfo = ToolInfo::fromJsonSchema([
            'type' => 'function',
            'function' => [
                'name' => 'get_weather',
                'description' => 'Get the current weather',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'location' => [
                            'type' => 'string',
                            'description' => 'The city and country',
                        ],
                        'unit' => [
                            'type' => 'string',
                            'description' => 'Temperature unit',
                            'enum' => ['celsius', 'fahrenheit'],
                        ],
                    ],
                    'required' => ['location'],
                ],
            ],
        ]);

        $this->assertSame(ToolTypeEnum::FUNCTION, $toolInfo->type);
        $this->assertSame('get_weather', $toolInfo->name);
        $this->assertSame('Get the current weather', $toolInfo->description);
        $this->assertCount(2, $toolInfo->parameters);
        $this->assertCount(1, $toolInfo->requiredParameters);

        $this->assertSame('location', $toolInfo->parameters[0]->name);
        $this->assertSame('string', $toolInfo->parameters[0]->type);

        $this->assertSame('unit', $toolInfo->parameters[1]->name);
        $this->assertSame(['celsius', 'fahrenheit'], $toolInfo->parameters[1]->enum);

        $this->assertSame('location', $toolInfo->requiredParameters[0]->name);
    }

    public function testFromJsonSchemaDirectFormat(): void
    {
        $toolInfo = ToolInfo::fromJsonSchema([
            'name' => 'calculator',
            'description' => 'Perform calculations',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'expression' => [
                        'type' => 'string',
                        'description' => 'The math expression',
                    ],
                ],
                'required' => ['expression'],
            ],
        ]);

        $this->assertSame('calculator', $toolInfo->name);
        $this->assertSame('Perform calculations', $toolInfo->description);
        $this->assertCount(1, $toolInfo->parameters);
        $this->assertCount(1, $toolInfo->requiredParameters);
    }

    public function testFromJsonSchemaWithNestedObject(): void
    {
        $toolInfo = ToolInfo::fromJsonSchema([
            'type' => 'function',
            'function' => [
                'name' => 'passport_control',
                'description' => 'Control passport status',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'horseName' => [
                            'type' => 'string',
                            'description' => 'Name of the horse',
                        ],
                        'context' => [
                            'type' => 'object',
                            'description' => 'Context data',
                            'properties' => [
                                'chipNumber' => [
                                    'type' => ['string', 'null'],
                                    'description' => 'The chip number',
                                ],
                                'hasSketch' => [
                                    'type' => ['boolean', 'null'],
                                    'description' => 'Has sketch',
                                ],
                            ],
                        ],
                    ],
                    'required' => ['horseName', 'context'],
                ],
            ],
        ]);

        $this->assertSame('passport_control', $toolInfo->name);
        $this->assertCount(2, $toolInfo->parameters);
        $this->assertCount(2, $toolInfo->requiredParameters);

        $horseName = $toolInfo->parameters[0];
        $this->assertSame('horseName', $horseName->name);
        $this->assertSame('string', $horseName->type);

        $context = $toolInfo->parameters[1];
        $this->assertSame('context', $context->name);
        $this->assertSame('object', $context->type);
        $this->assertIsArray($context->itemsOrProperties);
        $this->assertCount(2, $context->itemsOrProperties);

        /** @var Parameter $chipNumber */
        $chipNumber = $context->itemsOrProperties[0];
        $this->assertSame('chipNumber', $chipNumber->name);
        $this->assertSame('string', $chipNumber->type);
        $this->assertTrue($chipNumber->nullable);

        /** @var Parameter $hasSketch */
        $hasSketch = $context->itemsOrProperties[1];
        $this->assertSame('hasSketch', $hasSketch->name);
        $this->assertSame('boolean', $hasSketch->type);
        $this->assertTrue($hasSketch->nullable);
    }

    public function testFromJsonSchemaWithoutParameters(): void
    {
        $toolInfo = ToolInfo::fromJsonSchema([
            'type' => 'function',
            'function' => [
                'name' => 'get_time',
                'description' => 'Get current time',
            ],
        ]);

        $this->assertSame('get_time', $toolInfo->name);
        $this->assertCount(0, $toolInfo->parameters);
        $this->assertCount(0, $toolInfo->requiredParameters);
    }

    public function testFromJsonSchemaThrowsOnMissingName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Tool function must have a name');

        ToolInfo::fromJsonSchema([
            'type' => 'function',
            'function' => [
                'description' => 'Missing name',
            ],
        ]);
    }
}
