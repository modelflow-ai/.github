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

namespace ModelflowAi\FireworksAiAdapter\Tests\Unit\Image;

use ModelflowAi\DecisionTree\Criteria\CriteriaCollection;
use ModelflowAi\FireworksAiAdapter\Image\FireworksAiImageGenerationAdapter;
use ModelflowAi\Image\Request\Action\AIImageRequestActionInterface;
use ModelflowAi\Image\Request\Action\TextToImageAction;
use ModelflowAi\Image\Request\AIImageRequest;
use ModelflowAi\Image\Request\Value\ImageFormat;
use ModelflowAi\Image\Request\Value\OutputFormat;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Response\StreamableInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class FireworksAiImageAdapterTest extends TestCase
{
    use ProphecyTrait;

    public function testHandleRequestWithStream(): void
    {
        $httpResponse = $this->prophesize(StreamableInterface::class);
        $httpResponse->willImplement(ResponseInterface::class);
        $httpResponse->toStream()->willReturn(\fopen('https://placehold.co/1x1', 'r'));

        $httpClient = $this->prophesize(HttpClientInterface::class);
        $httpClient->request('POST', 'stable-diffusion-xl-1024-v1-0', [
            'headers' => [
                'Accept' => 'image/jpeg',
            ],
            'json' => [
                'cfg_scale' => 7,
                'height' => 1024,
                'width' => 1024,
                'steps' => 30,
                'seed' => 0,
                'safety_check' => false,
                'prompt' => 'cute cat',
            ],
        ])->willReturn($httpResponse->reveal());

        $adapter = new FireworksAiImageGenerationAdapter($httpClient->reveal(), 'stable-diffusion-xl-1024-v1-0');

        $response = $adapter->handleRequest(new AIImageRequest(
            new TextToImageAction('cute cat'),
            ImageFormat::JPEG,
            OutputFormat::STREAM,
            new CriteriaCollection([]),
            static fn () => null,
        ));

        $stream = \fopen('https://placehold.co/1x1', 'r');
        $this->assertNotFalse($stream);
        $this->assertSame(
            \stream_get_contents($stream),
            \stream_get_contents($response->stream()),
        );
    }

    public function testSupports(): void
    {
        $httpClient = HttpClient::create();

        $adapter = new FireworksAiImageGenerationAdapter($httpClient);

        $this->assertFalse($adapter->supports(new \stdClass()));
        $this->assertFalse($adapter->supports(new AIImageRequest(
            $this->prophesize(AIImageRequestActionInterface::class)->reveal(),
            ImageFormat::JPEG,
            OutputFormat::STREAM,
            new CriteriaCollection([]),
            static fn () => null,
        )));
        $this->assertTrue($adapter->supports(new AIImageRequest(
            new TextToImageAction('cute cat'),
            ImageFormat::JPEG,
            OutputFormat::STREAM,
            new CriteriaCollection([]),
            static fn () => null,
        )));
        $this->assertFalse($adapter->supports(new AIImageRequest(
            new TextToImageAction('cute cat'),
            ImageFormat::WEBP,
            OutputFormat::STREAM,
            new CriteriaCollection([]),
            static fn () => null,
        )));
    }
}
