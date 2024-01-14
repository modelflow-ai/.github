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

namespace ModelflowAi\Ollama\Tests\Unit;

use ModelflowAi\ApiClient\Transport\TransportInterface;
use ModelflowAi\Ollama\Client;
use ModelflowAi\Ollama\ClientInterface;
use ModelflowAi\Ollama\Resources\Chat;
use ModelflowAi\Ollama\Resources\Completion;
use ModelflowAi\Ollama\Resources\Embeddings;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

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

    public function testChat(): void
    {
        $client = $this->createInstance($this->transport->reveal());

        $chat = $client->chat();
        $this->assertInstanceOf(Chat::class, $chat);
    }

    public function testCompletion(): void
    {
        $client = $this->createInstance($this->transport->reveal());

        $chat = $client->completion();
        $this->assertInstanceOf(Completion::class, $chat);
    }

    public function testEmbeddings(): void
    {
        $client = $this->createInstance($this->transport->reveal());

        $chat = $client->embeddings();
        $this->assertInstanceOf(Embeddings::class, $chat);
    }

    private function createInstance(TransportInterface $transport): ClientInterface
    {
        return new Client($transport);
    }
}
