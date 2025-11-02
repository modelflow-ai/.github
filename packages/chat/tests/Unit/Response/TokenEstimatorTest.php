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

namespace ModelflowAi\Chat\Tests\Unit\Response;

use ModelflowAi\Chat\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Chat\Response\AIChatResponseMessage;
use ModelflowAi\Chat\Response\AIChatToolCall;
use ModelflowAi\Chat\Response\TokenEstimator;
use ModelflowAi\Chat\ToolInfo\ToolTypeEnum;
use PHPUnit\Framework\TestCase;

class TokenEstimatorTest extends TestCase
{
    public function testEstimateTokensWithEmptyString(): void
    {
        $this->assertSame(0, TokenEstimator::estimateTokens(''));
    }

    public function testEstimateTokensWithSimpleText(): void
    {
        $text = 'Hello World';
        $tokens = TokenEstimator::estimateTokens($text);

        // "Hello World" = 11 chars / 4 = 2.75, ceil = 3 tokens
        $this->assertGreaterThanOrEqual(1, $tokens);
        $this->assertLessThanOrEqual(10, $tokens);
    }

    public function testEstimateTokensWithLongText(): void
    {
        $text = 'This is a longer text that should result in more tokens being estimated based on the character count.';
        $tokens = TokenEstimator::estimateTokens($text);

        $expectedTokens = (int) \ceil(\mb_strlen($text) / 4.0);
        $this->assertGreaterThanOrEqual($expectedTokens - 10, $tokens);
        $this->assertLessThanOrEqual($expectedTokens + 20, $tokens);
    }

    public function testEstimateTokensWithSpecialCharacters(): void
    {
        $text = "Line 1\nLine 2\nLine 3";
        $tokens = TokenEstimator::estimateTokens($text);

        $this->assertGreaterThan(5, $tokens);
    }

    public function testEstimateTokensWithMultibyteCharacters(): void
    {
        $text = '你好世界'; // "Hello World" in Chinese
        $tokens = TokenEstimator::estimateTokens($text);

        $this->assertGreaterThanOrEqual(1, $tokens);
    }

    public function testEstimateTokensAlwaysReturnsAtLeastOne(): void
    {
        $text = 'a';
        $tokens = TokenEstimator::estimateTokens($text);

        $this->assertGreaterThanOrEqual(1, $tokens);
    }

    public function testEstimateMessageTokensWithSimpleMessage(): void
    {
        $message = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'Hello World',
        );
        $tokens = TokenEstimator::estimateMessageTokens($message);

        $this->assertGreaterThanOrEqual(5, $tokens);
    }

    public function testEstimateMessageTokensWithEmptyContent(): void
    {
        $message = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            '',
        );
        $tokens = TokenEstimator::estimateMessageTokens($message);

        $this->assertGreaterThanOrEqual(5, $tokens);
    }

    public function testEstimateMessageTokensWithToolCalls(): void
    {
        $toolCall = new AIChatToolCall(
            ToolTypeEnum::FUNCTION,
            'call-123',
            'test_function',
            ['arg1' => 'value1', 'arg2' => 42],
        );

        $message = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'Using a tool',
            [$toolCall],
        );
        $tokens = TokenEstimator::estimateMessageTokens($message);

        $this->assertGreaterThan(20, $tokens);
    }

    public function testEstimateMessageTokensWithMultipleToolCalls(): void
    {
        $toolCalls = [
            new AIChatToolCall(
                ToolTypeEnum::FUNCTION,
                'call-456',
                'function_one',
                ['key' => 'value'],
            ),
            new AIChatToolCall(
                ToolTypeEnum::FUNCTION,
                'call-789',
                'function_two',
                ['data' => 123],
            ),
        ];

        $message = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'Calling multiple tools',
            $toolCalls,
        );
        $tokens = TokenEstimator::estimateMessageTokens($message);

        $this->assertGreaterThan(30, $tokens);
    }

    public function testEstimateMessageTokensConsistency(): void
    {
        $message1 = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'Test message',
        );
        $message2 = new AIChatResponseMessage(
            AIChatMessageRoleEnum::USER,
            'Test message',
        );

        $tokens1 = TokenEstimator::estimateMessageTokens($message1);
        $tokens2 = TokenEstimator::estimateMessageTokens($message2);

        $this->assertSame($tokens1, $tokens2);
    }
}
