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

namespace ModelflowAi\OllamaAdapter\Tests\Unit\Image;

use ModelflowAi\DecisionTree\Criteria\CriteriaCollection;
use ModelflowAi\Image\Request\Action\AIImageRequestActionInterface;
use ModelflowAi\Image\Request\Action\TextToImageAction;
use ModelflowAi\Image\Request\AIImageRequest;
use ModelflowAi\Image\Request\Value\ImageFormat;
use ModelflowAi\Image\Request\Value\OutputFormat;
use ModelflowAi\OpenaiAdapter\Image\OpenAIImageGenerationAdapter;
use OpenAI\Resources\Images;
use OpenAI\Responses\Images\CreateResponse;
use OpenAI\Testing\ClientFake;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class OpenaiImageAdapterTest extends TestCase
{
    use ProphecyTrait;

    public function testHandleRequestWithB64(): void
    {
        $httpClient = $this->prophesize(HttpClientInterface::class);
        $client = new ClientFake([
            CreateResponse::from([
                'created' => 1_664_136_088,
                'data' => [
                    ['b64_json' => 'base64image'],
                ],
            ], CreateResponse::fakeResponseMetaInformation()),
        ]);

        $adapter = new OpenAIImageGenerationAdapter($httpClient->reveal(), $client, 'dall-e-3');

        $response = $adapter->handleRequest(new AIImageRequest(
            new TextToImageAction('cute cat'),
            ImageFormat::JPEG,
            OutputFormat::BASE64,
            new CriteriaCollection([]),
            static fn () => null,
        ));

        $client->assertSent(Images::class, static fn (string $method, array $parameters) => 'create' === $method
            && 'dall-e-3' === $parameters['model']
            && 'cute cat' === $parameters['prompt']
            && 1 === $parameters['n']
            && '1024x1024' === $parameters['size']
            && 'b64_json' === $parameters['response_format']);

        $this->assertSame('base64image', $response->resource);
    }

    public function testHandleRequestWithStream(): void
    {
        $httpClient = HttpClient::create();
        $client = new ClientFake([
            CreateResponse::from([
                'created' => 1_664_136_088,
                'data' => [
                    ['url' => 'https://placehold.co/1x1'],
                ],
            ], CreateResponse::fakeResponseMetaInformation()),
        ]);

        $adapter = new OpenAIImageGenerationAdapter($httpClient, $client, 'dall-e-3');

        $response = $adapter->handleRequest(new AIImageRequest(
            new TextToImageAction('cute cat'),
            ImageFormat::JPEG,
            OutputFormat::STREAM,
            new CriteriaCollection([]),
            static fn () => null,
        ));

        $client->assertSent(Images::class, static fn (string $method, array $parameters) => 'create' === $method
            && 'dall-e-3' === $parameters['model']
            && 'cute cat' === $parameters['prompt']
            && 1 === $parameters['n']
            && '1024x1024' === $parameters['size']
            && 'url' === $parameters['response_format']);

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
        $client = new ClientFake();

        $adapter = new OpenAIImageGenerationAdapter($httpClient, $client, 'dall-e-3');

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
    }
}
