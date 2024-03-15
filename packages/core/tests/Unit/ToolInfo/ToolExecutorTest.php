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

use ModelflowAi\Core\Request\Message\AIChatMessage;
use ModelflowAi\Core\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Core\Response\AIChatToolCall;
use ModelflowAi\Core\ToolInfo\ToolTypeEnum;
use PHPUnit\Framework\TestCase;

class ToolExecutorTest extends TestCase
{


    public function testHandleTool(): void
    {
        $chatRequest = $this->aiRequestHandler->createChatRequest(
            new AIChatMessage(AIChatMessageRoleEnum::USER, 'Test content'),
        )->tool('test', $this, 'toolMethod')->build();

        $result = $this->aiRequestHandler->handleTool(
            $chatRequest,
            new AIChatToolCall(ToolTypeEnum::FUNCTION, '123-123-123', 'test', ['test' => 'Test content']),
        );

        $this->assertInstanceOf(AIChatMessage::class, $result);
        $this->assertSame(AIChatMessageRoleEnum::TOOL, $result->role);

        $array = $result->toArray();
        $this->assertSame([
            'role' => AIChatMessageRoleEnum::TOOL->value,
            'content' => 'Test content',
            'tool_call_id' => '123-123-123',
            'name' => 'test',
        ], $array);
    }

    public function toolMethod(string $test): string
    {
        return $test;
    }
}
