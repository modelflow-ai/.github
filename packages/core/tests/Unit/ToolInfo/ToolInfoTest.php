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

use ModelflowAi\Core\ToolInfo\Parameter;
use ModelflowAi\Core\ToolInfo\ToolInfo;
use ModelflowAi\Core\ToolInfo\ToolTypeEnum;
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
}
