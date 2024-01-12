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

use ModelflowAi\Mistral\Client;
use ModelflowAi\Mistral\Responses\Chat\CreateResponse;
use ModelflowAi\Mistral\Responses\MetaInformation;
use ModelflowAi\Mistral\Tests\DataFixtures;
use ModelflowAi\Mistral\Transport\Enums\ContentType;
use ModelflowAi\Mistral\Transport\Enums\Method;
use ModelflowAi\Mistral\Transport\Payload;
use ModelflowAi\Mistral\Transport\Response\ObjectResponse;
use ModelflowAi\Mistral\Transport\TransportInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ChatTest extends TestCase
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

        $this->assertInstanceOf(CreateResponse::class, $response);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['id'], $response->id);
    }

    private function createInstance(TransportInterface $transport): Client
    {
        return new Client($transport);
    }
}
