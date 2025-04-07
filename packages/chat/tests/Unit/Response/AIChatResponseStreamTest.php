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

namespace ModelflowAi\Chat\Tests\Unit\Response;

use ModelflowAi\Chat\Request\AIChatStreamedRequest;
use ModelflowAi\Chat\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Chat\Response\AIChatResponseMessage;
use ModelflowAi\Chat\Response\AIChatResponseStream;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class AIChatResponseStreamTest extends TestCase
{
    use ProphecyTrait;

    public function testGetMessage(): void
    {
        $request = $this->prophesize(AIChatStreamedRequest::class);

        $response = new AIChatResponseStream($request->reveal(), new \ArrayIterator([
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Lorem'),
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Ipsum'),
        ]));

        $contents = ['Lorem', 'Ipsum'];
        foreach ($response->getMessageStream() as $key => $message) {
            $this->assertSame($contents[$key], $message->content);
        }

        $this->assertSame(AIChatMessageRoleEnum::ASSISTANT, $response->getMessage()->role);
        $this->assertSame('LoremIpsum', $response->getMessage()->content);
    }

    public function testGetUsage(): void
    {
        $request = $this->prophesize(AIChatStreamedRequest::class);

        $response = new AIChatResponseStream($request->reveal(), new \ArrayIterator([
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Lorem'),
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Ipsum'),
        ]));

        $this->assertNull($response->getUsage());
    }

    public function testGetMetadata(): void
    {
        $request = $this->prophesize(AIChatStreamedRequest::class);

        $response = new AIChatResponseStream($request->reveal(), new \ArrayIterator([
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Lorem'),
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Ipsum'),
        ]));

        $this->assertSame([], $response->getMetadata());
    }

    public function testGetRequest(): void
    {
        $request = $this->prophesize(AIChatStreamedRequest::class);

        $response = new AIChatResponseStream($request->reveal(), new \ArrayIterator([
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Lorem'),
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Ipsum'),
        ]));

        $this->assertSame($request->reveal(), $response->getRequest());
    }
}
