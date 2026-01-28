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

namespace ModelflowAi\Image\Tests\Unit\Request\Builder;

use ModelflowAi\Image\Request\Action\TextToImageAction;
use ModelflowAi\Image\Request\Builder\AIImageRequestBuilder;
use ModelflowAi\Image\Request\Builder\TextToImageActionBuilder;
use ModelflowAi\Image\Request\Value\ImageFormat;
use ModelflowAi\Image\Request\Value\OutputFormat;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class TextToImageActionBuilderTest extends TestCase
{
    use ProphecyTrait;

    public function testImageFormat(): void
    {
        $builder = AIImageRequestBuilder::create(static fn () => null);

        $textToImageBuilder = new TextToImageActionBuilder($builder, 'cute cat');
        $textToImageBuilder->imageFormat(ImageFormat::JPEG);

        $this->assertSame(
            ImageFormat::JPEG,
            $textToImageBuilder->build()->imageFormat,
        );
    }

    public function testAsStream(): void
    {
        $builder = AIImageRequestBuilder::create(static fn () => null);

        $textToImageBuilder = new TextToImageActionBuilder($builder, 'cute cat');
        $textToImageBuilder->asStream();

        $this->assertSame(
            OutputFormat::STREAM,
            $textToImageBuilder->build()->outputFormat,
        );
    }

    public function testAsBase64(): void
    {
        $builder = AIImageRequestBuilder::create(static fn () => null);

        $textToImageBuilder = new TextToImageActionBuilder($builder, 'cute cat');
        $textToImageBuilder->asBase64();

        $this->assertSame(
            OutputFormat::BASE64,
            $textToImageBuilder->build()->outputFormat,
        );
    }

    public function testBuild(): void
    {
        $builder = AIImageRequestBuilder::create(static fn () => null);

        $textToImageBuilder = new TextToImageActionBuilder($builder, 'cute cat');

        $action = $textToImageBuilder->build()->action;
        $this->assertInstanceOf(TextToImageAction::class, $action);
        $this->assertSame('cute cat', $action->prompt);
    }
}
