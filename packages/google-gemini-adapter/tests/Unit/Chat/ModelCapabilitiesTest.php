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

use ModelflowAi\GoogleGeminiAdapter\Chat\ModelCapabilities;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ModelCapabilitiesTest extends TestCase
{
    /**
     * @return \Generator<string, array{0: string, 1: bool}>
     */
    public static function thinkingLevelProvider(): \Generator
    {
        yield 'gemini-pro' => ['gemini-pro', false];
        yield 'gemini-1.5-flash' => ['models/gemini-1.5-flash', false];
        yield 'gemini-2.5-flash' => ['gemini-2.5-flash', false];
        yield 'gemini-3-pro' => ['gemini-3-pro', true];
        yield 'gemini-3.7-flash' => ['gemini-3.7-flash', true];
        yield 'gemini-3.7-flash qualified' => ['models/gemini-3.7-flash', true];
        yield 'gemini-30 is a different generation' => ['gemini-30-flash', false];
    }

    #[DataProvider('thinkingLevelProvider')]
    public function testSupportsThinkingLevel(string $model, bool $expected): void
    {
        $this->assertSame($expected, ModelCapabilities::supportsThinkingLevel($model));
    }

    /**
     * @return \Generator<string, array{0: string, 1: bool}>
     */
    public static function minimalThinkingLevelProvider(): \Generator
    {
        yield 'gemini-3.7-flash' => ['gemini-3.7-flash', true];
        yield 'gemini-3.7-flash qualified' => ['models/gemini-3.7-flash', true];
        yield 'gemini-3-pro has no minimal' => ['gemini-3-pro', false];
        yield 'gemini-2.5-flash does not take a thinking level at all' => ['gemini-2.5-flash', false];
    }

    #[DataProvider('minimalThinkingLevelProvider')]
    public function testSupportsMinimalThinkingLevel(string $model, bool $expected): void
    {
        $this->assertSame($expected, ModelCapabilities::supportsMinimalThinkingLevel($model));
    }
}
