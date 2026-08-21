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
        yield 'o3' => ['o3', false];
    }

    #[DataProvider('reasoningEffortNoneProvider')]
    public function testSupportsReasoningEffortNone(string $model, bool $expected): void
    {
        $this->assertSame($expected, ModelCapabilities::supportsReasoningEffortNone($model));
    }
}
