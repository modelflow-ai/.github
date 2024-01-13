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

namespace ModelflowAi\Mistral\Tests\Functional;

use ModelflowAi\ApiClient\Responses\MetaInformation;
use ModelflowAi\ApiClient\Transport\Enums\ContentType;
use ModelflowAi\ApiClient\Transport\Enums\Method;
use ModelflowAi\ApiClient\Transport\Payload;
use ModelflowAi\ApiClient\Transport\Response\ObjectResponse;
use ModelflowAi\ApiClient\Transport\TransportInterface;
use ModelflowAi\Mistral\Client;
use ModelflowAi\Mistral\ClientInterface;
use ModelflowAi\Mistral\Responses\Chat\CreateResponse as ChatCreateResponse;
use ModelflowAi\Mistral\Responses\Embeddings\CreateResponse as EmbeddingsChatCreateResponse;
use ModelflowAi\Mistral\Tests\DataFixtures;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ClientTest extends TestCase
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

    public function testChat(): void
    {
        $client = $this->createInstance($this->transport->reveal());

        $response = new ObjectResponse(DataFixtures::CHAT_CREATE_RESPONSE, MetaInformation::from([]));
        $this->transport->requestObject(
            Argument::that(fn (Payload $payload) => 'chat/completions' === $payload->resourceUri->uri
                && Method::POST === $payload->method
                && ContentType::JSON === $payload->contentType
                && DataFixtures::CHAT_CREATE_REQUEST === $payload->parameters),
        )->willReturn($response);

        $response = $client->chat()->create(DataFixtures::CHAT_CREATE_REQUEST);

        $this->assertInstanceOf(ChatCreateResponse::class, $response);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['id'], $response->id);
    }

    public function testEmbedding(): void
    {
        $client = $this->createInstance($this->transport->reveal());

        $response = new ObjectResponse(DataFixtures::EMBEDDINGS_CREATE_RESPONSE, MetaInformation::from([]));
        $this->transport->requestObject(
            Argument::that(fn (Payload $payload) => 'embeddings' === $payload->resourceUri->uri
                && Method::POST === $payload->method
                && ContentType::JSON === $payload->contentType
                && @\array_merge(DataFixtures::EMBEDDINGS_CREATE_REQUEST, ['encoding_format' => 'float']) === $payload->parameters),
        )->willReturn($response);

        $response = $client->embeddings()->create(DataFixtures::EMBEDDINGS_CREATE_REQUEST);

        $this->assertInstanceOf(EmbeddingsChatCreateResponse::class, $response);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['id'], $response->id);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['object'], $response->object);
        $this->assertCount(\count(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['data']), $response->data);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['data'][0]['object'], $response->data[0]->object);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['data'][0]['embedding'], $response->data[0]->embedding);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['data'][0]['index'], $response->data[0]->index);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['data'][1]['object'], $response->data[1]->object);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['data'][1]['embedding'], $response->data[1]->embedding);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['data'][1]['index'], $response->data[1]->index);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['model'], $response->model);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['usage']['prompt_tokens'], $response->usage->promptTokens);
        $this->assertNull($response->usage->completionTokens);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['usage']['total_tokens'], $response->usage->totalTokens);
        $this->assertInstanceOf(MetaInformation::class, $response->meta);
    }

    private function createInstance(TransportInterface $transport): ClientInterface
    {
        return new Client($transport);
    }
}
