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
use PHPUnit\Framework\TestCase;

class ParameterTest extends TestCase
{
    public function testName(): void
    {
        $message = new Parameter('name', 'string', 'Test description');

        $this->assertSame('name', $message->name);
    }

    public function testType(): void
    {
        $message = new Parameter('name', 'string', 'Test description');

        $this->assertSame('string', $message->type);
    }

    public function testDescription(): void
    {
        $message = new Parameter('name', 'string', 'Test description');

        $this->assertSame('Test description', $message->description);
    }

    public function testEnum(): void
    {
        $message = new Parameter('name', 'string', 'Test description', ['t1', 't2']);

        $this->assertSame(['t1', 't2'], $message->enum);
    }

    public function testFormat(): void
    {
        $message = new Parameter('name', 'string', 'Test description', ['t1', 't2'], 'json');

        $this->assertSame('json', $message->format);
    }

    public function testItemsOrProperty(): void
    {
        $message = new Parameter('name', 'string', 'Test description', ['t1', 't2'], 'json', 'TEST');

        $this->assertSame('TEST', $message->itemsOrProperties);
    }

    public function testNullable(): void
    {
        $parameter = new Parameter('name', 'string', 'Test description', [], null, null, true);

        $this->assertTrue($parameter->nullable);
    }

    public function testToArray(): void
    {
        $message = new Parameter('name', 'string', 'Test description', ['t1', 't2'], 'json', 'TEST', true);

        $this->assertSame([
            'name' => 'name',
            'type' => 'string',
            'description' => 'Test description',
            'enum' => ['t1', 't2'],
            'format' => 'json',
            'itemsOrProperties' => 'TEST',
            'nullable' => true,
            'required' => [],
        ], $message->toArray());
    }

    public function testFromJsonSchemaSimple(): void
    {
        $parameter = Parameter::fromJsonSchema('username', [
            'type' => 'string',
            'description' => 'The username',
        ]);

        $this->assertSame('username', $parameter->name);
        $this->assertSame('string', $parameter->type);
        $this->assertSame('The username', $parameter->description);
        $this->assertFalse($parameter->nullable);
    }

    public function testFromJsonSchemaNullableType(): void
    {
        $parameter = Parameter::fromJsonSchema('email', [
            'type' => ['string', 'null'],
            'description' => 'Optional email',
        ]);

        $this->assertSame('email', $parameter->name);
        $this->assertSame('string', $parameter->type);
        $this->assertSame('Optional email', $parameter->description);
        $this->assertTrue($parameter->nullable);
    }

    public function testFromJsonSchemaWithEnum(): void
    {
        $parameter = Parameter::fromJsonSchema('status', [
            'type' => 'string',
            'description' => 'The status',
            'enum' => ['active', 'inactive', 'pending'],
        ]);

        $this->assertSame('status', $parameter->name);
        $this->assertSame('string', $parameter->type);
        $this->assertSame(['active', 'inactive', 'pending'], $parameter->enum);
    }

    public function testFromJsonSchemaWithFormat(): void
    {
        $parameter = Parameter::fromJsonSchema('created_at', [
            'type' => 'string',
            'description' => 'Creation date',
            'format' => 'date-time',
        ]);

        $this->assertSame('created_at', $parameter->name);
        $this->assertSame('date-time', $parameter->format);
    }

    public function testFromJsonSchemaObjectType(): void
    {
        $parameter = Parameter::fromJsonSchema('context', [
            'type' => 'object',
            'description' => 'Context object',
            'properties' => [
                'chipNumber' => [
                    'type' => ['string', 'null'],
                    'description' => 'The chip number',
                ],
                'hasSketch' => [
                    'type' => ['boolean', 'null'],
                    'description' => 'Whether has sketch',
                ],
            ],
        ]);

        $this->assertSame('context', $parameter->name);
        $this->assertSame('object', $parameter->type);
        $this->assertIsArray($parameter->itemsOrProperties);
        $this->assertCount(2, $parameter->itemsOrProperties);

        /** @var Parameter $chipNumber */
        $chipNumber = $parameter->itemsOrProperties[0];
        $this->assertSame('chipNumber', $chipNumber->name);
        $this->assertSame('string', $chipNumber->type);
        $this->assertTrue($chipNumber->nullable);

        /** @var Parameter $hasSketch */
        $hasSketch = $parameter->itemsOrProperties[1];
        $this->assertSame('hasSketch', $hasSketch->name);
        $this->assertSame('boolean', $hasSketch->type);
        $this->assertTrue($hasSketch->nullable);
    }

    public function testFromJsonSchemaArrayOfStrings(): void
    {
        $parameter = Parameter::fromJsonSchema('tags', [
            'type' => 'array',
            'description' => 'List of tags',
            'items' => [
                'type' => 'string',
            ],
        ]);

        $this->assertSame('tags', $parameter->name);
        $this->assertSame('array', $parameter->type);
        $this->assertSame('string', $parameter->itemsOrProperties);
    }

    public function testFromJsonSchemaArrayOfObjects(): void
    {
        $parameter = Parameter::fromJsonSchema('items', [
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
        ]);

        $this->assertSame('items', $parameter->name);
        $this->assertSame('array', $parameter->type);
        $this->assertIsArray($parameter->itemsOrProperties);
        $this->assertCount(2, $parameter->itemsOrProperties);

        /** @var Parameter $id */
        $id = $parameter->itemsOrProperties[0];
        $this->assertSame('id', $id->name);
        $this->assertSame('integer', $id->type);

        /** @var Parameter $name */
        $name = $parameter->itemsOrProperties[1];
        $this->assertSame('name', $name->name);
        $this->assertSame('string', $name->type);
    }

    public function testFromJsonSchemaDefaults(): void
    {
        $parameter = Parameter::fromJsonSchema('empty', []);

        $this->assertSame('empty', $parameter->name);
        $this->assertSame('string', $parameter->type);
        $this->assertSame('', $parameter->description);
        $this->assertFalse($parameter->nullable);
    }

    public function testRequired(): void
    {
        $parameter = new Parameter('name', 'object', 'Test', [], null, null, false, ['field1', 'field2']);

        $this->assertSame(['field1', 'field2'], $parameter->required);
    }

    public function testFromJsonSchemaObjectWithRequired(): void
    {
        $parameter = Parameter::fromJsonSchema('user', [
            'type' => 'object',
            'description' => 'User object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                    'description' => 'User ID',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'User name',
                ],
                'email' => [
                    'type' => 'string',
                    'description' => 'User email',
                ],
            ],
            'required' => ['id', 'name'],
        ]);

        $this->assertSame('user', $parameter->name);
        $this->assertSame('object', $parameter->type);
        $this->assertSame(['id', 'name'], $parameter->required);
        $this->assertIsArray($parameter->itemsOrProperties);
        $this->assertCount(3, $parameter->itemsOrProperties);
    }

    public function testFromJsonSchemaArrayOfObjectsWithRequired(): void
    {
        $parameter = Parameter::fromJsonSchema('items', [
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
                    'optional' => [
                        'type' => 'string',
                        'description' => 'Optional field',
                    ],
                ],
                'required' => ['id', 'name'],
            ],
        ]);

        $this->assertSame('items', $parameter->name);
        $this->assertSame('array', $parameter->type);
        $this->assertSame(['id', 'name'], $parameter->required);
        $this->assertIsArray($parameter->itemsOrProperties);
        $this->assertCount(3, $parameter->itemsOrProperties);
    }

    public function testFromJsonSchemaRequiredWithInvalidItems(): void
    {
        $parameter = Parameter::fromJsonSchema('test', [
            'type' => 'object',
            'required' => ['valid', 123, null, 'another'],
        ]);

        $this->assertSame(['valid', 'another'], $parameter->required);
    }

    public function testFromJsonSchemaRequiredNotArray(): void
    {
        $parameter = Parameter::fromJsonSchema('test', [
            'type' => 'object',
            'required' => 'invalid',
        ]);

        $this->assertSame([], $parameter->required);
    }

    public function testToArrayWithNestedParameters(): void
    {
        $nestedProperties = [
            new Parameter('id', 'integer', 'The ID'),
            new Parameter('name', 'string', 'The name'),
        ];

        $parameter = new Parameter(
            name: 'user',
            type: 'object',
            description: 'User object',
            itemsOrProperties: $nestedProperties,
            required: ['id'],
        );

        $array = $parameter->toArray();

        $this->assertSame('user', $array['name']);
        $this->assertSame('object', $array['type']);
        $this->assertIsArray($array['itemsOrProperties']);
        $this->assertCount(2, $array['itemsOrProperties']);

        // Verify nested Parameters are serialized to arrays
        $this->assertIsArray($array['itemsOrProperties'][0]);
        $this->assertSame('id', $array['itemsOrProperties'][0]['name']);
        $this->assertSame('integer', $array['itemsOrProperties'][0]['type']);

        $this->assertIsArray($array['itemsOrProperties'][1]);
        $this->assertSame('name', $array['itemsOrProperties'][1]['name']);
        $this->assertSame('string', $array['itemsOrProperties'][1]['type']);
    }

    public function testToArrayWithStringItemsOrProperties(): void
    {
        $parameter = new Parameter(
            name: 'tags',
            type: 'array',
            description: 'List of tags',
            itemsOrProperties: 'string',
        );

        $array = $parameter->toArray();

        $this->assertSame('tags', $array['name']);
        $this->assertSame('string', $array['itemsOrProperties']);
    }
}
