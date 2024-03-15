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

namespace ModelflowAi\Core\Tests\Unit\Response;

use ModelflowAi\Core\Response\AIChatToolCall;
use ModelflowAi\Core\ToolInfo\ToolTypeEnum;
use PHPUnit\Framework\TestCase;

class AIChatToolCallTest extends TestCase
{
    public function testType(): void
    {
        $message = new AIChatToolCall(ToolTypeEnum::FUNCTION, '123-123-123', 'name', ['test' => 'test']);

        $this->assertSame(ToolTypeEnum::FUNCTION, $message->type);
    }

    public function testId(): void
    {
        $message = new AIChatToolCall(ToolTypeEnum::FUNCTION, '123-123-123', 'name', ['test' => 'test']);

        $this->assertSame('123-123-123', $message->id);
    }

    public function testName(): void
    {
        $message = new AIChatToolCall(ToolTypeEnum::FUNCTION, '123-123-123', 'name', ['test' => 'test']);

        $this->assertSame('name', $message->name);
    }

    public function testArguments(): void
    {
        $message = new AIChatToolCall(ToolTypeEnum::FUNCTION, '123-123-123', 'name', ['test' => 'test']);

        $this->assertSame(['test' => 'test'], $message->arguments);
    }
}
