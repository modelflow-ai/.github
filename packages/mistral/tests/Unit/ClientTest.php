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

namespace ModelflowAi\Mistral\Tests\Unit;

use ModelflowAi\ApiClient\Transport\TransportInterface;
use ModelflowAi\Mistral\Client;
use ModelflowAi\Mistral\ClientInterface;
use ModelflowAi\Mistral\Resources\Chat;
use ModelflowAi\Mistral\Resources\Embeddings;
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

    public function testEmbeddings(): void
    {
        $client = $this->createInstance($this->transport->reveal());

        $embeddings = $client->embeddings();
        $this->assertInstanceOf(Embeddings::class, $embeddings);
    }

    private function createInstance(TransportInterface $transport): ClientInterface
    {
        return new Client($transport);
    }
}
