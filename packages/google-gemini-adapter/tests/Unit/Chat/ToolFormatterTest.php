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

namespace ModelflowAi\GoogleGeminiAdapter\Tests\Unit\Chat;

use Gemini\Data\DataFormat;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;
use ModelflowAi\Chat\ToolInfo\Parameter;
use ModelflowAi\Chat\ToolInfo\ToolInfo;
use ModelflowAi\Chat\ToolInfo\ToolTypeEnum;
use ModelflowAi\GoogleGeminiAdapter\Chat\ToolFormatter;
use PHPUnit\Framework\TestCase;

final class ToolFormatterTest extends TestCase
{
    public function testFormatTool(): void
    {
        $tool = new ToolInfo(
            ToolTypeEnum::FUNCTION,
            'get_weather',
            'Get the current weather',
            [
                new Parameter('location', 'string', 'The location'),
                new Parameter('temperature_unit', 'string', 'Temperature unit', ['celsius', 'fahrenheit']),
                new Parameter('include_forecast', 'boolean', 'Include forecast'),
            ],
            [
                new Parameter('location', 'string', 'The location'),
            ],
        );

        $result = ToolFormatter::formatTool($tool);

        $this->assertSame('get_weather', $result->name);
        $this->assertSame('Get the current weather', $result->description);

        $parameters = $result->parameters;
        $this->assertInstanceOf(Schema::class, $parameters);
        $this->assertSame(DataType::OBJECT, $parameters->type);

        $properties = $parameters->properties;
        $this->assertNotNull($properties);
        $this->assertArrayHasKey('location', $properties);
        $this->assertArrayHasKey('temperature_unit', $properties);
        $this->assertArrayHasKey('include_forecast', $properties);
        $this->assertSame(DataType::STRING, $properties['location']->type);
        $this->assertSame(DataType::STRING, $properties['temperature_unit']->type);
        $this->assertSame(DataType::BOOLEAN, $properties['include_forecast']->type);
        $this->assertSame(['celsius', 'fahrenheit'], $properties['temperature_unit']->enum);
        $this->assertSame(['location'], $parameters->required);
    }

    public function testFormatTools(): void
    {
        $tools = [
            new ToolInfo(
                ToolTypeEnum::FUNCTION,
                'tool1',
                'First tool',
                [
                    new Parameter('param1', 'string', 'Parameter 1'),
                ],
                [],
            ),
            new ToolInfo(
                ToolTypeEnum::FUNCTION,
                'tool2',
                'Second tool',
                [
                    new Parameter('param2', 'integer', 'Parameter 2'),
                ],
                [],
            ),
        ];

        $result = ToolFormatter::formatTools($tools);

        $declarations = $result->functionDeclarations;
        $this->assertNotNull($declarations);
        $this->assertCount(2, $declarations);
        $this->assertSame('tool1', $declarations[0]->name);
        $this->assertSame('tool2', $declarations[1]->name);
    }

    public function testFormatToolWithNestedObject(): void
    {
        $tool = new ToolInfo(
            ToolTypeEnum::FUNCTION,
            'create_user',
            'Create a new user',
            [
                new Parameter(
                    'user',
                    'object',
                    'User details',
                    [],
                    null,
                    [
                        new Parameter('name', 'string', 'User name'),
                        new Parameter('age', 'integer', 'User age'),
                        new Parameter('email', 'string', 'User email'),
                    ],
                    false,
                    ['name', 'email'],
                ),
            ],
            [],
        );

        $result = ToolFormatter::formatTool($tool);

        $parameters = $result->parameters;
        $this->assertInstanceOf(Schema::class, $parameters);
        $properties = $parameters->properties;
        $this->assertNotNull($properties);

        $user = $properties['user'];
        $this->assertSame(DataType::OBJECT, $user->type);

        $userProperties = $user->properties;
        $this->assertNotNull($userProperties);
        $this->assertArrayHasKey('name', $userProperties);
        $this->assertArrayHasKey('age', $userProperties);
        $this->assertArrayHasKey('email', $userProperties);
        $this->assertSame(DataType::STRING, $userProperties['name']->type);
        $this->assertSame(DataType::INTEGER, $userProperties['age']->type);
        $this->assertSame(DataType::STRING, $userProperties['email']->type);
        $this->assertSame(['name', 'email'], $user->required);
    }

    public function testFormatToolWithArrayOfPrimitives(): void
    {
        $tool = new ToolInfo(
            ToolTypeEnum::FUNCTION,
            'send_emails',
            'Send emails to multiple recipients',
            [
                new Parameter(
                    'recipients',
                    'array',
                    'Email recipients',
                    [],
                    null,
                    'string',
                ),
            ],
            [],
        );

        $result = ToolFormatter::formatTool($tool);

        $parameters = $result->parameters;
        $this->assertInstanceOf(Schema::class, $parameters);
        $properties = $parameters->properties;
        $this->assertNotNull($properties);

        $recipients = $properties['recipients'];
        $this->assertSame(DataType::ARRAY, $recipients->type);
        $this->assertInstanceOf(Schema::class, $recipients->items);
        $this->assertSame(DataType::STRING, $recipients->items->type);
    }

    public function testFormatToolWithArrayOfObjects(): void
    {
        $tool = new ToolInfo(
            ToolTypeEnum::FUNCTION,
            'create_orders',
            'Create multiple orders',
            [
                new Parameter(
                    'orders',
                    'array',
                    'Order list',
                    [],
                    null,
                    [
                        new Parameter('product_id', 'integer', 'Product ID'),
                        new Parameter('quantity', 'integer', 'Quantity'),
                    ],
                    false,
                    ['product_id'],
                ),
            ],
            [],
        );

        $result = ToolFormatter::formatTool($tool);

        $parameters = $result->parameters;
        $this->assertInstanceOf(Schema::class, $parameters);
        $properties = $parameters->properties;
        $this->assertNotNull($properties);

        $orders = $properties['orders'];
        $this->assertSame(DataType::ARRAY, $orders->type);

        $items = $orders->items;
        $this->assertInstanceOf(Schema::class, $items);
        $this->assertSame(DataType::OBJECT, $items->type);

        $itemProperties = $items->properties;
        $this->assertNotNull($itemProperties);
        $this->assertArrayHasKey('product_id', $itemProperties);
        $this->assertArrayHasKey('quantity', $itemProperties);
        $this->assertSame(DataType::INTEGER, $itemProperties['product_id']->type);
        $this->assertSame(['product_id'], $items->required);
    }

    public function testFormatToolWithNullableParameter(): void
    {
        $tool = new ToolInfo(
            ToolTypeEnum::FUNCTION,
            'update_profile',
            'Update user profile',
            [
                new Parameter('bio', 'string', 'User bio', [], null, null, true),
                new Parameter('age', 'integer', 'User age', [], null, null, false),
            ],
            [],
        );

        $result = ToolFormatter::formatTool($tool);

        $parameters = $result->parameters;
        $this->assertInstanceOf(Schema::class, $parameters);
        $properties = $parameters->properties;
        $this->assertNotNull($properties);

        // Gemini expresses nullability through the dedicated `nullable` flag, not a type union.
        $this->assertSame(DataType::STRING, $properties['bio']->type);
        $this->assertTrue($properties['bio']->nullable);
        $this->assertSame(DataType::INTEGER, $properties['age']->type);
        $this->assertNull($properties['age']->nullable);
    }

    public function testFormatToolWithEnumParameter(): void
    {
        $tool = new ToolInfo(
            ToolTypeEnum::FUNCTION,
            'set_theme',
            'Set application theme',
            [
                new Parameter(
                    'theme',
                    'string',
                    'Theme name',
                    ['light', 'dark', 'auto'],
                ),
            ],
            [],
        );

        $result = ToolFormatter::formatTool($tool);

        $parameters = $result->parameters;
        $this->assertInstanceOf(Schema::class, $parameters);
        $properties = $parameters->properties;
        $this->assertNotNull($properties);
        $this->assertSame(['light', 'dark', 'auto'], $properties['theme']->enum);
    }

    public function testFormatToolWithOptionalParameters(): void
    {
        $tool = new ToolInfo(
            ToolTypeEnum::FUNCTION,
            'search',
            'Search for items',
            [
                new Parameter('query', 'string', 'Search query'),
                new Parameter('limit', 'integer', 'Result limit'),
                new Parameter('offset', 'integer', 'Result offset'),
            ],
            [],
        );

        $result = ToolFormatter::formatTool($tool);

        $parameters = $result->parameters;
        $this->assertInstanceOf(Schema::class, $parameters);
        $this->assertNull($parameters->required);
        $properties = $parameters->properties;
        $this->assertNotNull($properties);
        $this->assertCount(3, $properties);
    }

    public function testFormatToolWithFormatParameter(): void
    {
        $tool = new ToolInfo(
            ToolTypeEnum::FUNCTION,
            'schedule_event',
            'Schedule an event',
            [
                new Parameter(
                    'event_date',
                    'string',
                    'Event date',
                    [],
                    'date-time',
                ),
            ],
            [],
        );

        $result = ToolFormatter::formatTool($tool);

        $parameters = $result->parameters;
        $this->assertInstanceOf(Schema::class, $parameters);
        $properties = $parameters->properties;
        $this->assertNotNull($properties);
        $this->assertSame(DataFormat::DATETIME, $properties['event_date']->format);
    }

    public function testFormatToolDropsUnsupportedFormat(): void
    {
        $tool = new ToolInfo(
            ToolTypeEnum::FUNCTION,
            'register',
            'Register a user',
            [
                new Parameter('email', 'string', 'Email address', [], 'email'),
            ],
            [],
        );

        $result = ToolFormatter::formatTool($tool);

        $parameters = $result->parameters;
        $this->assertInstanceOf(Schema::class, $parameters);
        $properties = $parameters->properties;
        $this->assertNotNull($properties);
        $this->assertNull($properties['email']->format);
    }

    public function testFormatParameterThrowsExceptionForArrayWithoutItems(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Array type parameter must have items description');

        $tool = new ToolInfo(
            ToolTypeEnum::FUNCTION,
            'invalid_tool',
            'Tool with invalid array',
            [
                new Parameter('items', 'array', 'Items', [], null, null),
            ],
            [],
        );

        ToolFormatter::formatTool($tool);
    }

    public function testFormatParameterThrowsExceptionForObjectWithoutProperties(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Object type parameter must have properties description');

        $tool = new ToolInfo(
            ToolTypeEnum::FUNCTION,
            'invalid_tool',
            'Tool with invalid object',
            [
                new Parameter('config', 'object', 'Config', [], null, 'string'),
            ],
            [],
        );

        ToolFormatter::formatTool($tool);
    }
}
