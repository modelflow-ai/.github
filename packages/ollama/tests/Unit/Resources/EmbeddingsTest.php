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

namespace ModelflowAi\Ollama\Tests\Unit\Resources;

use ModelflowAi\ApiClient\Responses\MetaInformation;
use ModelflowAi\ApiClient\Transport\Enums\ContentType;
use ModelflowAi\ApiClient\Transport\Enums\Method;
use ModelflowAi\ApiClient\Transport\Payload;
use ModelflowAi\ApiClient\Transport\Response\ObjectResponse;
use ModelflowAi\ApiClient\Transport\TransportInterface;
use ModelflowAi\Ollama\Resources\Embeddings;
use ModelflowAi\Ollama\Resources\EmbeddingsInterface;
use ModelflowAi\Ollama\Responses\Embeddings\CreateResponse;
use ModelflowAi\Ollama\Tests\DataFixtures;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class EmbeddingsTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<TransportInterface>
     */
    private ObjectProphecy $transport;

    protected function setUp(): void
    {
        $this->transport = $this->prophesize(TransportInterface::class);
    }

    public function testCreate(): void
    {
        $response = new ObjectResponse(DataFixtures::EMBEDDINGS_CREATE_RESPONSE, MetaInformation::from([]));
        $this->transport->requestObject(
            Argument::that(fn (Payload $payload) => 'embeddings' === $payload->resourceUri->uri
            && Method::POST === $payload->method
            && ContentType::JSON === $payload->contentType
            && DataFixtures::EMBEDDINGS_CREATE_REQUEST === $payload->parameters),
        )->willReturn($response);

        $embeddings = $this->createInstance($this->transport->reveal());

        $result = $embeddings->create(DataFixtures::EMBEDDINGS_CREATE_REQUEST);

        $this->assertInstanceOf(CreateResponse::class, $result);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['embedding'], $result->embedding);
        $this->assertInstanceOf(MetaInformation::class, $result->meta);
    }

    public function testCreateMissingModel(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $embeddings = $this->createInstance($this->transport->reveal());

        $parameters = [
            'prompt' => DataFixtures::EMBEDDINGS_CREATE_REQUEST['prompt'],
        ];

        // @phpstan-ignore-next-line
        $embeddings->create($parameters);
    }

    public function testCreateMissingPrompt(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $embeddings = $this->createInstance($this->transport->reveal());

        $parameters = [
            'model' => DataFixtures::EMBEDDINGS_CREATE_REQUEST['model'],
        ];

        // @phpstan-ignore-next-line
        $embeddings->create($parameters);
    }

    private function createInstance(TransportInterface $transport): EmbeddingsInterface
    {
        return new Embeddings($transport);
    }
}
