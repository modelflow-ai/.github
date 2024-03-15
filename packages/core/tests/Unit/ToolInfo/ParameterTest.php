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
}
