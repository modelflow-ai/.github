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

namespace ModelflowAi\Core\Tests\Unit\Request\Builder;

use ModelflowAi\Core\Request\AICompletionRequest;
use ModelflowAi\Core\Request\Builder\AICompletionRequestBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class AICompletionRequestBuilderTest extends TestCase
{
    use ProphecyTrait;

    public function testAsJson(): void
    {
        $builder = new AICompletionRequestBuilder(fn () => null);
        $builder->prompt('Test text');

        $builder->asJson();

        $this->assertSame('json', $builder->build()->getOption('format'));
    }

    public function testPrompt(): void
    {
        $builder = new AICompletionRequestBuilder(fn () => null);
        $prompt = 'Test text';

        $builder->prompt($prompt);

        $this->assertSame($prompt, $builder->build()->getPrompt());
    }

    public function testBuild(): void
    {
        $builder = new AICompletionRequestBuilder(fn () => null);
        $prompt = 'Test text';

        $builder->prompt($prompt);

        $this->assertInstanceOf(
            AICompletionRequest::class,
            $builder->build(),
        );
    }

    public function testBuildThrowsExceptionWhenNoPromptGiven(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No text given');

        $builder = new AICompletionRequestBuilder(fn () => null);
        $builder->build();
    }
}
