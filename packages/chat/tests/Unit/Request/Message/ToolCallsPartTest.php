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

namespace ModelflowAi\Chat\Tests\Unit\Request\Message;

use ModelflowAi\Chat\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Chat\Request\Message\ToolCallsPart;
use ModelflowAi\Chat\Response\AIChatToolCall;
use ModelflowAi\Chat\ToolInfo\ToolTypeEnum;
use PHPUnit\Framework\TestCase;

class ToolCallsPartTest extends TestCase
{
    public function testConstruct(): void
    {
        $toolCalls = [
            new AIChatToolCall(ToolTypeEnum::FUNCTION, '123-123-123', 'name', ['test' => 'test']),
        ];
        $toolCallsPart = new ToolCallsPart($toolCalls);

        $this->assertSame($toolCalls, $toolCallsPart->toolCalls);
    }

    public function testEnhanceMessage(): void
    {
        $toolCalls = [
            new AIChatToolCall(ToolTypeEnum::FUNCTION, '123-123-123', 'name', ['test' => 'test']),
        ];
        $toolCallsPart = new ToolCallsPart($toolCalls);

        $result = [
            'role' => AIChatMessageRoleEnum::USER->value,
            'content' => '',
        ];
        $expectedResult = [
            'role' => AIChatMessageRoleEnum::USER->value,
            'content' => '',
            'tool_calls' => [
                [
                    'id' => '123-123-123',
                    'type' => ToolTypeEnum::FUNCTION->value,
                    'function' => [
                        'name' => 'name',
                        'arguments' => '{"test":"test"}',
                    ],
                ],
            ],
        ];

        $this->assertSame($expectedResult, $toolCallsPart->enhanceMessage($result));
    }

    public function testEnhanceMessageWithoutArguments(): void
    {
        $toolCalls = [
            new AIChatToolCall(ToolTypeEnum::FUNCTION, '123-123-123', 'name', []),
        ];
        $toolCallsPart = new ToolCallsPart($toolCalls);

        $result = [
            'role' => AIChatMessageRoleEnum::USER->value,
            'content' => '',
        ];
        $expectedResult = [
            'role' => AIChatMessageRoleEnum::USER->value,
            'content' => '',
            'tool_calls' => [
                [
                    'id' => '123-123-123',
                    'type' => ToolTypeEnum::FUNCTION->value,
                    'function' => [
                        'name' => 'name',
                        'arguments' => '{}',
                    ],
                ],
            ],
        ];

        $this->assertSame($expectedResult, $toolCallsPart->enhanceMessage($result));
    }

    public function testEnhanceMessageWithSignature(): void
    {
        $toolCalls = [
            new AIChatToolCall(ToolTypeEnum::FUNCTION, '123-123-123', 'name', ['test' => 'test'], 'signature-123'),
        ];
        $toolCallsPart = new ToolCallsPart($toolCalls);

        $result = [
            'role' => AIChatMessageRoleEnum::USER->value,
            'content' => '',
        ];
        $expectedResult = [
            'role' => AIChatMessageRoleEnum::USER->value,
            'content' => '',
            'tool_calls' => [
                [
                    'id' => '123-123-123',
                    'type' => ToolTypeEnum::FUNCTION->value,
                    'function' => [
                        'name' => 'name',
                        'arguments' => '{"test":"test"}',
                    ],
                    'signature' => 'signature-123',
                ],
            ],
        ];

        $this->assertSame($expectedResult, $toolCallsPart->enhanceMessage($result));
    }
}
