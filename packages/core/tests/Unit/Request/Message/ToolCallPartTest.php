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

namespace ModelflowAi\Core\Tests\Unit\Request\Message;

use ModelflowAi\Core\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Core\Request\Message\ToolCallPart;
use PHPUnit\Framework\TestCase;

class ToolCallPartTest extends TestCase
{
    public function testConstruct(): void
    {
        $toolCallId = '123-123-123';
        $toolName = 'test';
        $content = '{"test": "test"}';
        $toolCallPart = new ToolCallPart($toolCallId, $toolName, $content);

        $this->assertSame($toolCallId, $toolCallPart->toolCallId);
        $this->assertSame($toolName, $toolCallPart->toolName);
        $this->assertSame($content, $toolCallPart->content);
    }

    public function testEnhanceMessage(): void
    {
        $toolCallId = '123-123-123';
        $toolName = 'test';
        $content = '{"test": "test"}';
        $toolCallPart = new ToolCallPart($toolCallId, $toolName, $content);

        $result = [
            'role' => AIChatMessageRoleEnum::USER->value,
            'content' => '',
        ];
        $expectedResult = [
            'role' => AIChatMessageRoleEnum::USER->value,
            'content' => $content,
            'tool_call_id' => $toolCallId,
            'name' => $toolName,
        ];

        $this->assertSame($expectedResult, $toolCallPart->enhanceMessage($result));
    }
}
