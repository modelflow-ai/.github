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

namespace ModelflowAi\Mistral\Tests\Unit\Resources;

use ModelflowAi\ApiClient\Responses\MetaInformation;
use ModelflowAi\ApiClient\Transport\Enums\ContentType;
use ModelflowAi\ApiClient\Transport\Enums\Method;
use ModelflowAi\ApiClient\Transport\Payload;
use ModelflowAi\ApiClient\Transport\Response\ObjectResponse;
use ModelflowAi\ApiClient\Transport\TransportInterface;
use ModelflowAi\Mistral\Resources\Embeddings;
use ModelflowAi\Mistral\Resources\EmbeddingsInterface;
use ModelflowAi\Mistral\Responses\Embeddings\CreateResponse;
use ModelflowAi\Mistral\Tests\DataFixtures;
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
            && @\array_merge(DataFixtures::EMBEDDINGS_CREATE_REQUEST, ['encoding_format' => 'float']) === $payload->parameters),
        )->willReturn($response);

        $embeddings = $this->createInstance($this->transport->reveal());

        $result = $embeddings->create(DataFixtures::EMBEDDINGS_CREATE_REQUEST);

        $this->assertInstanceOf(CreateResponse::class, $result);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['id'], $result->id);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['object'], $result->object);
        $this->assertCount(\count(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['data']), $result->data);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['data'][0]['object'], $result->data[0]->object);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['data'][0]['embedding'], $result->data[0]->embedding);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['data'][0]['index'], $result->data[0]->index);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['data'][1]['object'], $result->data[1]->object);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['data'][1]['embedding'], $result->data[1]->embedding);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['data'][1]['index'], $result->data[1]->index);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['model'], $result->model);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['usage']['prompt_tokens'], $result->usage->promptTokens);
        $this->assertNull($result->usage->completionTokens);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['usage']['total_tokens'], $result->usage->totalTokens);
        $this->assertInstanceOf(MetaInformation::class, $result->meta);
    }

    public function testCreateMissingModel(): void
    {
        \error_reporting(\E_ALL);
        \ini_set('display_errors', '1');

        $response = new ObjectResponse(DataFixtures::EMBEDDINGS_CREATE_RESPONSE, MetaInformation::from([]));
        $this->transport->requestObject(
            Argument::that(fn (Payload $payload) => 'embeddings' === $payload->resourceUri->uri
                && Method::POST === $payload->method
                && ContentType::JSON === $payload->contentType
                && @\array_diff($payload->parameters, DataFixtures::EMBEDDINGS_CREATE_REQUEST) === ['encoding_format' => 'float']),
        )->willReturn($response);

        $embeddings = $this->createInstance($this->transport->reveal());

        $parameters = [
            'input' => DataFixtures::EMBEDDINGS_CREATE_REQUEST['input'],
        ];

        $result = $embeddings->create($parameters);

        $this->assertInstanceOf(CreateResponse::class, $result);
    }

    public function testCreateMissingInput(): void
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
