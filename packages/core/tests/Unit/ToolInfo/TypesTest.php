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

namespace ModelflowAi\Core\Tests\Unit\ToolInfo;

use ModelflowAi\Core\ToolInfo\Types;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class TypesTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @return array<int, array{0: string, 1: string}>
     */
    public static function provideData(): array
    {
        return [
            ['string', 'string'],
            ['int', 'integer'],
            ['float', 'number'],
            ['bool', 'boolean'],
            ['array', 'array'],
        ];
    }

    /**
     * @dataProvider provideData
     */
    public function testMapPhpTypeToJsonSchemaType(string $actual, string $expected): void
    {
        $type = $this->prophesize(\ReflectionNamedType::class);
        $type->getName()->willReturn($actual);

        $this->assertSame($expected, Types::mapPhpTypeToJsonSchemaType($type->reveal()));
    }

    public function testMapPhpTypeToJsonSchemaTypeException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $type = $this->prophesize(\ReflectionNamedType::class);
        $type->getName()->willReturn('mixed');

        Types::mapPhpTypeToJsonSchemaType($type->reveal());
    }
}
