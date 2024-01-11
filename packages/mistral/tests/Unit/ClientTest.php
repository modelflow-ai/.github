<?php

namespace ModelflowAi\Mistral\Tests\Unit;

use ModelflowAi\Mistral\Client;
use ModelflowAi\Mistral\Resources\Chat;
use ModelflowAi\Mistral\Transport\Payload;
use ModelflowAi\Mistral\Transport\SymfonyHttpTransporter;
use ModelflowAi\Mistral\Transport\Response\ObjectResponse;
use ModelflowAi\Mistral\Transport\TransportInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class ClientTest extends TestCase
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

    public function testRequest(): void
    {
        $client = $this->createInstance($this->transport->reveal());

        $chat = $client->chat();
        $this->assertInstanceOf(Chat::class, $chat);
    }

    private function createInstance(TransportInterface $transport): Client
    {
        return new Client($transport);
    }
}
