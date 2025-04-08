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

namespace ModelflowAi\Chat\Tests\Unit\Adapter\Fake;

use ModelflowAi\Chat\Adapter\Fake\FakeChatAdapter;
use ModelflowAi\Chat\AIChatRequestHandlerInterface;
use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\Chat\Request\AIChatStreamedRequest;
use ModelflowAi\Chat\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Chat\Response\AIChatResponse;
use ModelflowAi\Chat\Response\AIChatResponseMessage;
use ModelflowAi\Chat\Response\AIChatResponseStream;
use ModelflowAi\Chat\Response\Usage;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class FakeChatAdapterTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<AIChatRequest>
     */
    private ObjectProphecy $request;

    /**
     * @var ObjectProphecy<AIChatStreamedRequest>
     */
    private ObjectProphecy $streamedRequest;

    private FakeChatAdapter $adapter;

    protected function setUp(): void
    {
        $this->request = $this->prophesize(AIChatRequest::class);
        $this->streamedRequest = $this->prophesize(AIChatStreamedRequest::class);
        $this->streamedRequest->willImplement(AIChatRequestHandlerInterface::class);

        $this->adapter = new FakeChatAdapter();
    }

    public function testSupports(): void
    {
        $this->assertTrue($this->adapter->supports($this->request->reveal()));
        $this->assertTrue($this->adapter->supports($this->streamedRequest->reveal()));

        $nonRequest = new \stdClass();
        $this->assertFalse($this->adapter->supports($nonRequest));
    }

    public function testHandleRequestWithSingleMessage(): void
    {
        // Create a response message
        $responseMessage = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'Test response',
            null,
        );

        // Add the message to the adapter
        $this->adapter->addMessage($responseMessage);

        // Handle the request
        $response = $this->adapter->handleRequest($this->request->reveal());

        // Verify the response
        $this->assertInstanceOf(AIChatResponse::class, $response);
        $this->assertSame($responseMessage, $response->getMessage());
        $this->assertSame($this->request->reveal(), $response->getRequest());

        // Default usage should be 0,0,0
        $this->assertSame(0, $response->getUsage()?->totalTokens);
    }

    public function testHandleRequestWithCustomUsage(): void
    {
        // Create a response message
        $responseMessage = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'Test response',
            null,
        );

        // Create a usage object
        $usage = new Usage(10, 20, 30);

        // Add the message with usage to the adapter
        $this->adapter->addMessage($responseMessage, $usage);

        // Handle the request
        $response = $this->adapter->handleRequest($this->request->reveal());

        // Verify the response
        $this->assertInstanceOf(AIChatResponse::class, $response);
        $this->assertSame($responseMessage, $response->getMessage());
        $this->assertSame($usage, $response->getUsage());
    }

    public function testHandleStreamedRequestWithSingleMessage(): void
    {
        // Create a response message
        $responseMessage = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'Test response',
            null,
        );

        // Add the message to the adapter
        $this->adapter->addMessage($responseMessage);

        // Handle the request
        $response = $this->adapter->handleRequest($this->streamedRequest->reveal());

        // Verify the response
        $this->assertInstanceOf(AIChatResponseStream::class, $response);
        $this->assertSame($this->streamedRequest->reveal(), $response->getRequest());

        // Check the streamed messages
        $messages = \iterator_to_array($response->getMessageStream());
        $this->assertCount(1, $messages);
        $this->assertSame($responseMessage, $messages[0]);
    }

    public function testHandleStreamedRequestWithMultipleMessages(): void
    {
        // Create response messages
        $responseMessage1 = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'First response',
            null,
        );

        $responseMessage2 = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'Second response',
            null,
        );

        // Add the messages to the adapter
        $this->adapter->addMessage([$responseMessage1, $responseMessage2]);

        // Handle the request
        $response = $this->adapter->handleRequest($this->streamedRequest->reveal());

        // Verify the response
        $this->assertInstanceOf(AIChatResponseStream::class, $response);

        // Check the streamed messages
        $messages = \iterator_to_array($response->getMessageStream());
        $this->assertCount(2, $messages);
        $this->assertSame($responseMessage1, $messages[0]);
        $this->assertSame($responseMessage2, $messages[1]);
    }

    public function testHandleStreamedRequestWithCustomUsage(): void
    {
        // Create response messages
        $responseMessage1 = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'First response',
            null,
        );

        $responseMessage2 = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'Second response',
            null,
        );

        // Create a usage object
        $usage = new Usage(10, 20, 30);

        // Add the messages with usage to the adapter
        $this->adapter->addMessage([$responseMessage1, $responseMessage2], $usage);

        // Handle the request
        $response = $this->adapter->handleRequest($this->streamedRequest->reveal());

        // Verify the response
        $this->assertInstanceOf(AIChatResponseStream::class, $response);
        $this->assertSame($usage, $response->getUsage());
    }

    public function testMultipleRequests(): void
    {
        // Create first response message
        $responseMessage1 = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'First response',
            null,
        );

        // Create second response message
        $responseMessage2 = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'Second response',
            null,
        );

        // Add both messages to the adapter
        $this->adapter->addMessage($responseMessage1);
        $this->adapter->addMessage($responseMessage2);

        // Handle first request
        $response1 = $this->adapter->handleRequest($this->request->reveal());

        // Verify first response
        $this->assertInstanceOf(AIChatResponse::class, $response1);
        $this->assertSame($responseMessage1, $response1->getMessage());

        // Handle second request
        $response2 = $this->adapter->handleRequest($this->request->reveal());

        // Verify second response
        $this->assertInstanceOf(AIChatResponse::class, $response2);
        $this->assertSame($responseMessage2, $response2->getMessage());
    }

    public function testExceptionWhenNoMessagesAvailable(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        // Try to handle a request without adding any messages
        $this->adapter->handleRequest($this->request->reveal());
    }
}
