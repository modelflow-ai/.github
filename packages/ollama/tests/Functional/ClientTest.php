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

namespace ModelflowAi\Ollama\Tests\Functional;

use ModelflowAi\ApiClient\Responses\MetaInformation;
use ModelflowAi\ApiClient\Transport\Enums\ContentType;
use ModelflowAi\ApiClient\Transport\Enums\Method;
use ModelflowAi\ApiClient\Transport\Payload;
use ModelflowAi\ApiClient\Transport\Response\ObjectResponse;
use ModelflowAi\ApiClient\Transport\TransportInterface;
use ModelflowAi\Ollama\Client;
use ModelflowAi\Ollama\ClientInterface;
use ModelflowAi\Ollama\Responses\Chat\CreateResponse as ChatCreateResponse;
use ModelflowAi\Ollama\Responses\Completion\CreateResponse as CompletionCreateResponse;
use ModelflowAi\Ollama\Responses\Embeddings\CreateResponse as EmbeddingsCreateResponse;
use ModelflowAi\Ollama\Tests\DataFixtures;
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
            Argument::that(fn (Payload $payload) => 'chat' === $payload->resourceUri->uri
                && Method::POST === $payload->method
                && ContentType::JSON === $payload->contentType
                && @\array_merge(DataFixtures::CHAT_CREATE_REQUEST, ['stream' => false]) === $payload->parameters),
        )->willReturn($response);

        $response = $client->chat()->create(DataFixtures::CHAT_CREATE_REQUEST);

        $this->assertInstanceOf(ChatCreateResponse::class, $response);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['message']['content'], $response->message->content);
    }

    public function testCompletion(): void
    {
        $client = $this->createInstance($this->transport->reveal());

        $response = new ObjectResponse(DataFixtures::COMPLETION_CREATE_RESPONSE, MetaInformation::from([]));
        $this->transport->requestObject(
            Argument::that(fn (Payload $payload) => 'generate' === $payload->resourceUri->uri
                && Method::POST === $payload->method
                && ContentType::JSON === $payload->contentType
                && @\array_merge(DataFixtures::COMPLETION_CREATE_REQUEST, ['format' => 'json', 'stream' => false]) === $payload->parameters),
        )->willReturn($response);

        $response = $client->completion()->create(DataFixtures::COMPLETION_CREATE_REQUEST);

        $this->assertInstanceOf(CompletionCreateResponse::class, $response);
        $this->assertSame(DataFixtures::COMPLETION_CREATE_RESPONSE['response'], $response->response);
    }

    public function testEmbedding(): void
    {
        $client = $this->createInstance($this->transport->reveal());

        $response = new ObjectResponse(DataFixtures::EMBEDDINGS_CREATE_RESPONSE, MetaInformation::from([]));
        $this->transport->requestObject(
            Argument::that(fn (Payload $payload) => 'embeddings' === $payload->resourceUri->uri
                && Method::POST === $payload->method
                && ContentType::JSON === $payload->contentType
                && DataFixtures::EMBEDDINGS_CREATE_REQUEST === $payload->parameters),
        )->willReturn($response);

        $response = $client->embeddings()->create(DataFixtures::EMBEDDINGS_CREATE_REQUEST);

        $this->assertInstanceOf(EmbeddingsCreateResponse::class, $response);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['embedding'], $response->embedding);
    }

    private function createInstance(TransportInterface $transport): ClientInterface
    {
        return new Client($transport);
    }
}
