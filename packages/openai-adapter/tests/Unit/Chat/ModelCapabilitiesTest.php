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

namespace ModelflowAi\OpenaiAdapter\Tests\Unit\Chat;

use ModelflowAi\OpenaiAdapter\Chat\ModelCapabilities;
use ModelflowAi\OpenaiAdapter\Chat\ReasoningEffortEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ModelCapabilitiesTest extends TestCase
{
    /**
     * @return \Generator<string, array{0: string, 1: bool}>
     */
    public static function samplingProvider(): \Generator
    {
        yield 'gpt-4' => ['gpt-4', true];
        yield 'gpt-4o' => ['gpt-4o', true];
        yield 'gpt-5' => ['gpt-5', false];
        yield 'gpt-5.6-sol' => ['gpt-5.6-sol', false];
        yield 'gpt-6-sol' => ['gpt-6-sol', false];
        yield 'gpt-6.1-sol' => ['gpt-6.1-sol', false];
        yield 'gpt-6.1-terra' => ['gpt-6.1-terra', false];
        yield 'gpt-6-luna' => ['gpt-6-luna', false];
        yield 'o1' => ['o1', false];
        yield 'o3-mini' => ['o3-mini', false];
    }

    #[DataProvider('samplingProvider')]
    public function testSupportsSampling(string $model, bool $expected): void
    {
        $this->assertSame($expected, ModelCapabilities::supportsSampling($model));
    }

    /**
     * @return \Generator<string, array{0: string, 1: bool}>
     */
    public static function reasoningEffortNoneProvider(): \Generator
    {
        yield 'gpt-4' => ['gpt-4', false];
        yield 'gpt-5.4' => ['gpt-5.4', false];
        yield 'gpt-5.5' => ['gpt-5.5', false];
        yield 'gpt-5.6' => ['gpt-5.6', true];
        yield 'gpt-5.6-sol' => ['gpt-5.6-sol', true];
        yield 'gpt-5.6-terra' => ['gpt-5.6-terra', true];
        yield 'gpt-5.6-luna' => ['gpt-5.6-luna', true];
        yield 'gpt-6-sol' => ['gpt-6-sol', true];
        yield 'gpt-6.1-terra' => ['gpt-6.1-terra', true];
        yield 'gpt-6-luna' => ['gpt-6-luna', true];
        yield 'gpt-6.1-sol has no none' => ['gpt-6.1-sol', false];
        yield 'gpt-6-astra has no none' => ['gpt-6-astra', false];
        yield 'o3' => ['o3', false];
    }

    #[DataProvider('reasoningEffortNoneProvider')]
    public function testSupportsReasoningEffortNone(string $model, bool $expected): void
    {
        $this->assertSame($expected, ModelCapabilities::supportsReasoningEffortNone($model));
    }

    /**
     * @return \Generator<string, array{0: string, 1: ReasoningEffortEnum, 2: bool}>
     */
    public static function reasoningEffortProvider(): \Generator
    {
        yield 'gpt-5.6 knows none' => ['gpt-5.6-sol', ReasoningEffortEnum::NONE, true];
        yield 'gpt-5.6 dropped minimal' => ['gpt-5.6-sol', ReasoningEffortEnum::MINIMAL, false];
        yield 'gpt-5.6 knows xhigh' => ['gpt-5.6-terra', ReasoningEffortEnum::XHIGH, true];
        yield 'gpt-5.6 knows max' => ['gpt-5.6-luna', ReasoningEffortEnum::MAX, true];
        yield 'gpt-6 knows none' => ['gpt-6-sol', ReasoningEffortEnum::NONE, true];
        yield 'gpt-6 knows xhigh' => ['gpt-6.1-terra', ReasoningEffortEnum::XHIGH, true];
        yield 'gpt-6 has no max' => ['gpt-6-luna', ReasoningEffortEnum::MAX, false];
        yield 'gpt-6 has no minimal' => ['gpt-6-sol', ReasoningEffortEnum::MINIMAL, false];
        yield 'gpt-6.1-sol refuses none' => ['gpt-6.1-sol', ReasoningEffortEnum::NONE, false];
        yield 'gpt-6.1-sol knows low' => ['gpt-6.1-sol', ReasoningEffortEnum::LOW, true];
        yield 'gpt-6-astra refuses none' => ['gpt-6-astra', ReasoningEffortEnum::NONE, false];
        yield 'unknown model refuses none' => ['o3', ReasoningEffortEnum::NONE, false];
        yield 'unknown model keeps minimal' => ['o3', ReasoningEffortEnum::MINIMAL, true];
        yield 'unknown model keeps high' => ['o3', ReasoningEffortEnum::HIGH, true];
    }

    #[DataProvider('reasoningEffortProvider')]
    public function testSupportsReasoningEffort(string $model, ReasoningEffortEnum $effort, bool $expected): void
    {
        $this->assertSame($expected, ModelCapabilities::supportsReasoningEffort($model, $effort));
    }

    /**
     * @return \Generator<string, array{0: string, 1: bool}>
     */
    public static function toolsRequireNoReasoningProvider(): \Generator
    {
        yield 'gpt-5.6-sol' => ['gpt-5.6-sol', true];
        yield 'gpt-6-sol' => ['gpt-6-sol', true];
        yield 'gpt-6.1-terra' => ['gpt-6.1-terra', true];
        yield 'gpt-6-luna' => ['gpt-6-luna', true];
        yield 'gpt-6.1-sol cannot ask for none' => ['gpt-6.1-sol', false];
        yield 'gpt-6-astra cannot ask for none' => ['gpt-6-astra', false];
        yield 'gpt-4o' => ['gpt-4o', false];
    }

    #[DataProvider('toolsRequireNoReasoningProvider')]
    public function testToolsRequireNoReasoning(string $model, bool $expected): void
    {
        $this->assertSame($expected, ModelCapabilities::toolsRequireNoReasoning($model));
    }
}
