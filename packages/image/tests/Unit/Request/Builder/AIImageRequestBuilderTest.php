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

use ModelflowAi\DecisionTree\Criteria\CapabilityCriteria;
use ModelflowAi\Image\Request\Action\AIImageRequestActionInterface;
use ModelflowAi\Image\Request\Action\TextToImageAction;
use ModelflowAi\Image\Request\Builder\AIImageRequestBuilder;
use ModelflowAi\Image\Request\Builder\TextToImageActionBuilder;
use ModelflowAi\Image\Request\Value\ImageFormat;
use ModelflowAi\Image\Request\Value\OutputFormat;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class AIImageRequestBuilderTest extends TestCase
{
    use ProphecyTrait;

    public function testImageFormat(): void
    {
        $action = $this->prophesize(AIImageRequestActionInterface::class)->reveal();

        $builder = AIImageRequestBuilder::create(static fn () => null);
        $builder->imageFormat(ImageFormat::JPEG);

        $this->assertSame(
            ImageFormat::JPEG,
            $builder->build($action)->imageFormat,
        );
    }

    public function testAddCriteria(): void
    {
        $action = $this->prophesize(AIImageRequestActionInterface::class)->reveal();

        $builder = AIImageRequestBuilder::create(static fn () => null);
        $builder->addCriteria(CapabilityCriteria::BASIC);

        $this->assertSame(
            [CapabilityCriteria::BASIC],
            $builder->build($action)->criteriaCollection->all,
        );
    }

    public function testTextToImage(): void
    {
        $builder = AIImageRequestBuilder::create(static fn () => null);
        $actionBuilder = $builder->textToImage('cute cat');

        $this->assertInstanceOf(
            TextToImageActionBuilder::class,
            $actionBuilder,
        );

        $action = $actionBuilder->build()->action;
        $this->assertInstanceOf(TextToImageAction::class, $action);
        $this->assertSame('cute cat', $action->prompt);
    }

    public function testAs(): void
    {
        $action = $this->prophesize(AIImageRequestActionInterface::class)->reveal();

        $builder = AIImageRequestBuilder::create(static fn () => null);
        $builder->as(OutputFormat::BASE64);

        $this->assertSame(
            OutputFormat::BASE64,
            $builder->build($action)->outputFormat,
        );
    }

    public function testBuild(): void
    {
        $action = $this->prophesize(AIImageRequestActionInterface::class)->reveal();

        $builder = AIImageRequestBuilder::create(static fn () => null);

        $this->assertSame(
            $action,
            $builder->build($action)->action,
        );
    }
}
