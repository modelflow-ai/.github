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

namespace ModelflowAi\MistralAdapter\Tests\Unit\Chat;

use ModelflowAi\MistralAdapter\Chat\ModelCapabilities;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ModelCapabilitiesTest extends TestCase
{
    /**
     * @return \Generator<string, array{0: string, 1: bool}>
     */
    public static function reasoningEffortProvider(): \Generator
    {
        yield 'mistral-small-latest' => ['mistral-small-latest', true];
        yield 'mistral-medium-latest' => ['mistral-medium-latest', true];
        yield 'mistral-medium-3-5' => ['mistral-medium-3-5', true];
        yield 'mistral-large-latest' => ['mistral-large-latest', false];
        yield 'mistral-tiny' => ['mistral-tiny', false];
        yield 'a snapshot from before the family became hybrid' => ['mistral-medium-2508', false];
        yield 'zai-glm-5-2' => ['zai-glm-5-2', false];
    }

    #[DataProvider('reasoningEffortProvider')]
    public function testSupportsReasoningEffort(string $model, bool $expected): void
    {
        $this->assertSame($expected, ModelCapabilities::supportsReasoningEffort($model));
    }
}
