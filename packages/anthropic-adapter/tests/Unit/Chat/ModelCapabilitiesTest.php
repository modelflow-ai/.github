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

namespace ModelflowAi\AnthropicAdapter\Tests\Unit\Chat;

use ModelflowAi\Anthropic\Model;
use ModelflowAi\AnthropicAdapter\Chat\ModelCapabilities;
use ModelflowAi\AnthropicAdapter\Chat\ThinkingModeEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ModelCapabilitiesTest extends TestCase
{
    /**
     * @return \Generator<string, array{0: string, 1: bool}>
     */
    public static function samplingProvider(): \Generator
    {
        yield 'claude-3-haiku' => [Model::CLAUDE_3_HAIKU->value, true];
        yield 'claude-haiku-4-5 snapshot' => ['claude-haiku-4-5-20251001', true];
        yield 'claude-sonnet-4-6' => ['claude-sonnet-4-6', true];
        yield 'claude-opus-4-6' => ['claude-opus-4-6', true];
        yield 'claude-opus-4-7' => ['claude-opus-4-7', false];
        yield 'claude-opus-4-8' => ['claude-opus-4-8', false];
        yield 'claude-opus-5' => ['claude-opus-5', false];
        yield 'claude-sonnet-5' => ['claude-sonnet-5', false];
        yield 'claude-fable-5' => ['claude-fable-5', false];
        yield 'claude-mythos-preview' => ['claude-mythos-preview', false];
        yield 'claude-opus-5-5' => ['claude-opus-5-5', false];
        yield 'claude-sonnet-5-5' => ['claude-sonnet-5-5', false];
        yield 'claude-haiku-5-5' => ['claude-haiku-5-5', false];
        yield 'claude-fable-5-1' => ['claude-fable-5-1', false];
    }

    #[DataProvider('samplingProvider')]
    public function testSupportsSampling(string $model, bool $expected): void
    {
        $this->assertSame($expected, ModelCapabilities::supportsSampling($model));
    }

    /**
     * @return \Generator<string, array{0: string, 1: bool}>
     */
    public static function prefillProvider(): \Generator
    {
        yield 'claude-3-haiku' => [Model::CLAUDE_3_HAIKU->value, true];
        yield 'claude-haiku-4-5 snapshot' => ['claude-haiku-4-5-20251001', true];
        yield 'claude-opus-4-5' => ['claude-opus-4-5', true];
        yield 'claude-sonnet-4-5' => ['claude-sonnet-4-5', true];
        yield 'claude-opus-4-6' => ['claude-opus-4-6', false];
        yield 'claude-sonnet-4-6' => ['claude-sonnet-4-6', false];
        yield 'claude-opus-4-7' => ['claude-opus-4-7', false];
        yield 'claude-opus-4-8' => ['claude-opus-4-8', false];
        yield 'claude-opus-5' => ['claude-opus-5', false];
        yield 'claude-opus-5-5' => ['claude-opus-5-5', false];
        yield 'claude-sonnet-5' => ['claude-sonnet-5', false];
        yield 'claude-sonnet-5-5' => ['claude-sonnet-5-5', false];
        yield 'claude-haiku-5-5' => ['claude-haiku-5-5', false];
        yield 'claude-fable-5' => ['claude-fable-5', false];
        yield 'claude-fable-5-1' => ['claude-fable-5-1', false];
        yield 'claude-mythos-5' => ['claude-mythos-5', false];
        yield 'claude-mythos-preview' => ['claude-mythos-preview', false];
    }

    #[DataProvider('prefillProvider')]
    public function testSupportsPrefill(string $model, bool $expected): void
    {
        $this->assertSame($expected, ModelCapabilities::supportsPrefill($model));
    }

    /**
     * @return \Generator<string, array{0: string, 1: bool}>
     */
    public static function thinkingProvider(): \Generator
    {
        yield 'claude-3-haiku' => [Model::CLAUDE_3_HAIKU->value, false];
        yield 'claude-haiku-4-5 snapshot' => ['claude-haiku-4-5-20251001', false];
        yield 'claude-sonnet-4-6' => ['claude-sonnet-4-6', true];
        yield 'claude-opus-4-8' => ['claude-opus-4-8', true];
        yield 'claude-sonnet-5' => ['claude-sonnet-5', true];
        yield 'claude-fable-5' => ['claude-fable-5', true];
        yield 'claude-mythos-preview' => ['claude-mythos-preview', true];
        yield 'claude-opus-5-5' => ['claude-opus-5-5', true];
        yield 'claude-sonnet-5-5' => ['claude-sonnet-5-5', true];
        yield 'claude-haiku-5-5' => ['claude-haiku-5-5', true];
    }

    #[DataProvider('thinkingProvider')]
    public function testSupportsThinking(string $model, bool $expected): void
    {
        $this->assertSame($expected, ModelCapabilities::supportsThinking($model));
    }

    /**
     * @return \Generator<string, array{0: string, 1: bool}>
     */
    public static function thinksByDefaultProvider(): \Generator
    {
        yield 'claude-3-haiku' => [Model::CLAUDE_3_HAIKU->value, false];
        yield 'claude-sonnet-4-6' => ['claude-sonnet-4-6', false];
        yield 'claude-opus-4-7' => ['claude-opus-4-7', false];
        yield 'claude-opus-4-8' => ['claude-opus-4-8', false];
        yield 'claude-opus-5' => ['claude-opus-5', true];
        yield 'claude-sonnet-5' => ['claude-sonnet-5', true];
        yield 'claude-fable-5' => ['claude-fable-5', true];
        yield 'claude-mythos-preview' => ['claude-mythos-preview', true];
        yield 'claude-haiku-5-5' => ['claude-haiku-5-5', true];
    }

    #[DataProvider('thinksByDefaultProvider')]
    public function testThinksByDefault(string $model, bool $expected): void
    {
        $this->assertSame($expected, ModelCapabilities::thinksByDefault($model));
    }

    /**
     * @return \Generator<string, array{0: string, 1: bool}>
     */
    public static function disabledThinkingProvider(): \Generator
    {
        yield 'claude-3-haiku' => [Model::CLAUDE_3_HAIKU->value, false];
        yield 'claude-opus-4-8' => ['claude-opus-4-8', true];
        yield 'claude-opus-5' => ['claude-opus-5', true];
        yield 'claude-sonnet-5' => ['claude-sonnet-5', true];
        yield 'claude-fable-5' => ['claude-fable-5', false];
        yield 'claude-mythos-5' => ['claude-mythos-5', false];
        yield 'claude-mythos-preview' => ['claude-mythos-preview', false];
        yield 'claude-opus-5-5' => ['claude-opus-5-5', false];
        yield 'claude-sonnet-5-5' => ['claude-sonnet-5-5', false];
        yield 'claude-haiku-5-5' => ['claude-haiku-5-5', true];
    }

    #[DataProvider('disabledThinkingProvider')]
    public function testSupportsDisabledThinking(string $model, bool $expected): void
    {
        $this->assertSame($expected, ModelCapabilities::supportsDisabledThinking($model));
    }

    /**
     * @return \Generator<string, array{0: string, 1: ThinkingModeEnum|null}>
     */
    public static function disabledThinkingModeProvider(): \Generator
    {
        yield 'claude-sonnet-5' => ['claude-sonnet-5', ThinkingModeEnum::DISABLED];
        yield 'claude-sonnet-5-5' => ['claude-sonnet-5-5', ThinkingModeEnum::BETWEEN_TOOLS];
        yield 'claude-sonnet-5-5 snapshot' => ['claude-sonnet-5-5-20261001', ThinkingModeEnum::BETWEEN_TOOLS];
        yield 'claude-haiku-5-5' => ['claude-haiku-5-5', ThinkingModeEnum::DISABLED];
        yield 'claude-opus-5-5' => ['claude-opus-5-5', null];
        yield 'claude-fable-5' => ['claude-fable-5', null];
        yield 'claude-3-haiku' => [Model::CLAUDE_3_HAIKU->value, null];
    }

    #[DataProvider('disabledThinkingModeProvider')]
    public function testDisabledThinkingMode(string $model, ?ThinkingModeEnum $expected): void
    {
        $this->assertSame($expected, ModelCapabilities::disabledThinkingMode($model));
    }
}
