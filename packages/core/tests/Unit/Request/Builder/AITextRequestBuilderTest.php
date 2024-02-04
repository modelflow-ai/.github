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

use ModelflowAi\Core\Request\AITextRequest;
use ModelflowAi\Core\Request\Builder\AITextRequestBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class AITextRequestBuilderTest extends TestCase
{
    use ProphecyTrait;

    public function testText(): void
    {
        $builder = new AITextRequestBuilder(fn () => null);
        $text = 'Test text';

        $builder->text($text);

        $this->assertSame($text, $builder->build()->getText());
    }

    public function testBuild(): void
    {
        $builder = new AITextRequestBuilder(fn () => null);
        $text = 'Test text';

        $builder->text($text);

        $this->assertInstanceOf(
            AITextRequest::class,
            $builder->build(),
        );
    }

    public function testBuildThrowsExceptionWhenNoTextGiven(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No text given');

        $builder = new AITextRequestBuilder(fn () => null);
        $builder->build();
    }
}
