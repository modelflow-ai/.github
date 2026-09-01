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

namespace ModelflowAi\StabilityAdapter\Tests\Unit\Image;

use ModelflowAi\DecisionTree\Criteria\CriteriaCollection;
use ModelflowAi\Image\Request\Action\AIImageRequestActionInterface;
use ModelflowAi\Image\Request\Action\TextToImageAction;
use ModelflowAi\Image\Request\AIImageRequest;
use ModelflowAi\Image\Request\Value\ImageFormat;
use ModelflowAi\Image\Request\Value\OutputFormat;
use ModelflowAi\Stability\ClientInterface;
use ModelflowAi\Stability\Resources\GenerateUltraInterface;
use ModelflowAi\StabilityAdapter\Image\StabilityImageAdapter;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class StabilityImageAdapterTest extends TestCase
{
    use ProphecyTrait;

    public function testHandleRequestWithBase64(): void
    {
        $generateUltra = $this->prophesize(GenerateUltraInterface::class);
        $client = $this->prophesize(ClientInterface::class);
        $client->generateUltra()->willReturn($generateUltra->reveal());

        // Use reflection to create GenerateBase64Response instance without constructor
        $reflection = new \ReflectionClass(\ModelflowAi\Stability\Responses\Generate\GenerateBase64Response::class);
        $stabilityResponse = $reflection->newInstanceWithoutConstructor();

        // Use reflection to set the readonly base64 property
        $property = $reflection->getProperty('base64');
        $property->setValue($stabilityResponse, 'base64encodedimage');

        $generateUltra->generateAsBase64(['prompt' => 'cute cat'])
            ->shouldBeCalled()
            ->willReturn($stabilityResponse);

        $adapter = new StabilityImageAdapter($client->reveal());

        $result = $adapter->handleRequest(new AIImageRequest(
            new TextToImageAction('cute cat'),
            ImageFormat::PNG,
            OutputFormat::BASE64,
            new CriteriaCollection([]),
            fn () => null,
        ));

        $this->assertSame('base64encodedimage', $result->resource);
        $this->assertSame(ImageFormat::PNG, $result->imageFormat);
    }

    public function testHandleRequestWithStream(): void
    {
        $generateUltra = $this->prophesize(GenerateUltraInterface::class);
        $client = $this->prophesize(ClientInterface::class);
        $client->generateUltra()->willReturn($generateUltra->reveal());

        $stream = \fopen('php://memory', 'r+');
        $this->assertNotFalse($stream);
        \fwrite($stream, 'test image data');
        \rewind($stream);

        // Use reflection to create GenerateFileResponse instance without constructor
        $reflection = new \ReflectionClass(\ModelflowAi\Stability\Responses\Generate\GenerateFileResponse::class);
        $stabilityResponse = $reflection->newInstanceWithoutConstructor();

        // Use reflection to set the readonly resource property
        $property = $reflection->getProperty('resource');
        $property->setValue($stabilityResponse, $stream);

        $generateUltra->generateAsResource(['prompt' => 'cute cat'])
            ->shouldBeCalled()
            ->willReturn($stabilityResponse);

        $adapter = new StabilityImageAdapter($client->reveal());

        $result = $adapter->handleRequest(new AIImageRequest(
            new TextToImageAction('cute cat'),
            ImageFormat::PNG,
            OutputFormat::STREAM,
            new CriteriaCollection([]),
            fn () => null,
        ));

        $this->assertIsResource($result->stream());
        $this->assertSame('test image data', \stream_get_contents($result->stream()));
        $this->assertSame(ImageFormat::PNG, $result->imageFormat);
    }

    public function testSupports(): void
    {
        $client = $this->prophesize(ClientInterface::class);
        $adapter = new StabilityImageAdapter($client->reveal());

        $this->assertFalse($adapter->supports(new \stdClass()));
        $this->assertFalse($adapter->supports(new AIImageRequest(
            $this->prophesize(AIImageRequestActionInterface::class)->reveal(),
            ImageFormat::PNG,
            OutputFormat::STREAM,
            new CriteriaCollection([]),
            fn () => null,
        )));
        $this->assertTrue($adapter->supports(new AIImageRequest(
            new TextToImageAction('cute cat'),
            ImageFormat::PNG,
            OutputFormat::STREAM,
            new CriteriaCollection([]),
            fn () => null,
        )));
    }
}
