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

use ModelflowAi\Chat\Response\TokenEstimator;
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

        // Should be roughly text length / 4
        $expectedTokens = (int) \ceil(\mb_strlen($text) / 4.0);
        $this->assertGreaterThanOrEqual($expectedTokens - 10, $tokens);
        $this->assertLessThanOrEqual($expectedTokens + 20, $tokens);
    }

    public function testEstimateTokensWithSpecialCharacters(): void
    {
        $text = "Line 1\nLine 2\nLine 3";
        $tokens = TokenEstimator::estimateTokens($text);

        // Should add overhead for newlines
        $this->assertGreaterThan(5, $tokens);
    }

    public function testEstimateTokensWithMultibyteCharacters(): void
    {
        $text = '你好世界'; // "Hello World" in Chinese
        $tokens = TokenEstimator::estimateTokens($text);

        // Should handle multibyte characters correctly
        $this->assertGreaterThanOrEqual(1, $tokens);
    }

    public function testEstimateTokensAlwaysReturnsAtLeastOne(): void
    {
        $text = 'a';
        $tokens = TokenEstimator::estimateTokens($text);

        $this->assertGreaterThanOrEqual(1, $tokens);
    }
}
