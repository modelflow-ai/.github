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

namespace ModelflowAi\Chat\Tests\Unit\Middleware\Tools;

use ModelflowAi\Chat\Adapter\AIChatAdapterInterface;
use ModelflowAi\Chat\Middleware\Tools\ToolStreamResponseDecorator;
use ModelflowAi\Chat\Request\AIChatStreamedRequest;
use ModelflowAi\Chat\Request\Message\AIChatMessage;
use ModelflowAi\Chat\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Chat\Response\AIChatResponseMessage;
use ModelflowAi\Chat\Response\AIChatResponseStream;
use ModelflowAi\Chat\Response\AIChatResponseStreamInterface;
use ModelflowAi\Chat\Response\AIChatToolCall;
use ModelflowAi\Chat\Response\StreamingUsageTracker;
use ModelflowAi\Chat\Response\Usage;
use ModelflowAi\Chat\ToolInfo\ToolExecutor;
use ModelflowAi\Chat\ToolInfo\ToolTypeEnum;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ToolStreamResponseDecoratorTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<AIChatStreamedRequest>
     */
    private ObjectProphecy $request;

    /**
     * @var ObjectProphecy<AIChatAdapterInterface>
     */
    private ObjectProphecy $adapter;

    /**
     * @var ObjectProphecy<ToolExecutor>
     */
    private ObjectProphecy $toolExecutor;

    private const MAX_TOOL_EXECUTIONS = 3;

    protected function setUp(): void
    {
        $this->request = $this->prophesize(AIChatStreamedRequest::class);
        $this->adapter = $this->prophesize(AIChatAdapterInterface::class);
        $this->toolExecutor = $this->prophesize(ToolExecutor::class);

        $this->request->getMetadata()->willReturn(['key' => 'value']);
        $this->request->getTools()->willReturn([]);
    }

    public function testGetMessageReturnsOriginalMessage(): void
    {
        // Create a message for the stream
        $responseMessage = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'Hello',
            null,
        );

        // Create a stream with the message
        $messageIterator = new \ArrayIterator([$responseMessage]);

        // Mock original stream
        $originalStream = $this->prophesize(AIChatResponseStreamInterface::class);
        $originalStream->getMessageStream()->willReturn($messageIterator);
        $originalStream->getMessage()->willReturn($responseMessage);
        $originalStream->getUsage()->willReturn(new Usage(10, 20, 30));

        $nextMiddleware = function ($request, $adapter) use ($originalStream) {
            return $originalStream->reveal();
        };

        $decorator = new ToolStreamResponseDecorator(
            $originalStream->reveal(),
            $this->request->reveal(),
            $this->adapter->reveal(),
            $nextMiddleware,
            $this->toolExecutor->reveal(),
            self::MAX_TOOL_EXECUTIONS,
        );

        $this->assertSame($responseMessage, $decorator->getMessage());
    }

    public function testGetRequestReturnsRequest(): void
    {
        // Create a message for the stream
        $responseMessage = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'Hello',
            null,
        );

        // Mock original stream
        $originalStream = $this->prophesize(AIChatResponseStreamInterface::class);
        $originalStream->getMessageStream()->willReturn(new \ArrayIterator([$responseMessage]));
        $originalStream->getMessage()->willReturn($responseMessage);
        $originalStream->getUsage()->willReturn(new Usage(10, 20, 30));

        $nextMiddleware = function ($request, $adapter) use ($originalStream) {
            return $originalStream->reveal();
        };

        $decorator = new ToolStreamResponseDecorator(
            $originalStream->reveal(),
            $this->request->reveal(),
            $this->adapter->reveal(),
            $nextMiddleware,
            $this->toolExecutor->reveal(),
            self::MAX_TOOL_EXECUTIONS,
        );

        $this->assertSame($this->request->reveal(), $decorator->getRequest());
    }

    public function testGetUsageReturnsUsage(): void
    {
        // Create a usage object
        $usage = new Usage(10, 20, 30);

        // Create a message for the stream
        $responseMessage = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'Hello',
            null,
        );

        // Mock original stream
        $originalStream = $this->prophesize(AIChatResponseStreamInterface::class);
        $originalStream->getMessageStream()->willReturn(new \ArrayIterator([$responseMessage]));
        $originalStream->getMessage()->willReturn($responseMessage);
        $originalStream->getUsage()->willReturn($usage);

        $nextMiddleware = function ($request, $adapter) use ($originalStream) {
            return $originalStream->reveal();
        };

        $decorator = new ToolStreamResponseDecorator(
            $originalStream->reveal(),
            $this->request->reveal(),
            $this->adapter->reveal(),
            $nextMiddleware,
            $this->toolExecutor->reveal(),
            self::MAX_TOOL_EXECUTIONS,
        );

        $this->assertSame($usage, $decorator->getUsage());
    }

    public function testGetMetadataReturnsMetadata(): void
    {
        // Create a message for the stream
        $responseMessage = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'Hello',
            null,
        );

        // Mock original stream
        $originalStream = $this->prophesize(AIChatResponseStreamInterface::class);
        $originalStream->getMessageStream()->willReturn(new \ArrayIterator([$responseMessage]));
        $originalStream->getMessage()->willReturn($responseMessage);
        $originalStream->getUsage()->willReturn(new Usage(10, 20, 30));

        $nextMiddleware = function ($request, $adapter) use ($originalStream) {
            return $originalStream->reveal();
        };

        $decorator = new ToolStreamResponseDecorator(
            $originalStream->reveal(),
            $this->request->reveal(),
            $this->adapter->reveal(),
            $nextMiddleware,
            $this->toolExecutor->reveal(),
            self::MAX_TOOL_EXECUTIONS,
        );

        $this->assertSame(['key' => 'value'], $decorator->getMetadata());
    }

    public function testGetMessageStreamWithNoToolCalls(): void
    {
        // Create messages with no tool calls
        $message1 = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'First message',
            null,
        );

        $message2 = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'Second message',
            null,
        );

        // Mock original stream
        $originalStream = $this->prophesize(AIChatResponseStreamInterface::class);
        $originalStream->getMessageStream()->willReturn(new \ArrayIterator([$message1, $message2]));
        $originalStream->getMessage()->willReturn($message1);
        $originalStream->getUsage()->willReturn(new Usage(10, 20, 30));

        $nextMiddleware = function ($request, $adapter) use ($originalStream) {
            return $originalStream->reveal();
        };

        $decorator = new ToolStreamResponseDecorator(
            $originalStream->reveal(),
            $this->request->reveal(),
            $this->adapter->reveal(),
            $nextMiddleware,
            $this->toolExecutor->reveal(),
            self::MAX_TOOL_EXECUTIONS,
        );

        $resultMessages = \iterator_to_array($decorator->getMessageStream());

        $this->assertCount(2, $resultMessages);
        $this->assertSame($message1, $resultMessages[0]);
        $this->assertSame($message2, $resultMessages[1]);
    }

    public function testGetMessageStreamWithEmptyToolCalls(): void
    {
        // Create messages with empty tool calls array
        $message1 = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'First message',
            [],
        );

        $message2 = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'Second message',
            [],
        );

        // Mock original stream
        $originalStream = $this->prophesize(AIChatResponseStreamInterface::class);
        $originalStream->getMessageStream()->willReturn(new \ArrayIterator([$message1, $message2]));
        $originalStream->getMessage()->willReturn($message1);
        $originalStream->getUsage()->willReturn(new Usage(10, 20, 30));

        $nextMiddleware = function ($request, $adapter) use ($originalStream) {
            return $originalStream->reveal();
        };

        $decorator = new ToolStreamResponseDecorator(
            $originalStream->reveal(),
            $this->request->reveal(),
            $this->adapter->reveal(),
            $nextMiddleware,
            $this->toolExecutor->reveal(),
            self::MAX_TOOL_EXECUTIONS,
        );

        $resultMessages = \iterator_to_array($decorator->getMessageStream());

        $this->assertCount(2, $resultMessages);
        $this->assertSame($message1, $resultMessages[0]);
        $this->assertSame($message2, $resultMessages[1]);
    }

    public function testGetMessageStreamWithToolCalls(): void
    {
        // Mock getTools() to include the tool
        $this->request->getTools()->willReturn(['tool_name' => [(object) [], 'method']]);

        // Create a tool call
        $toolCall = new AIChatToolCall(
            ToolTypeEnum::FUNCTION,
            'id_123',
            'tool_name',
            ['param' => 'value'],
        );

        // Create messages with tool calls
        $message1 = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'First message',
            null,
        );

        $message2 = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'Second message',
            [$toolCall],
        );

        // Create original stream
        $messageIterator = new \ArrayIterator([$message1, $message2]);
        $usageTracker = new StreamingUsageTracker();
        $usageTracker->updateUsage(new Usage(10, 20, 30), true);
        $originalStream = new AIChatResponseStream(
            $this->request->reveal(),
            $messageIterator,
            [],
            $usageTracker,
        );

        // Mock the request handling
        $updatedRequest = $this->prophesize(AIChatStreamedRequest::class);
        $updatedRequest->getTools()->willReturn(['tool_name' => [(object) [], 'method']]);
        $this->request->withMessage(Argument::type(AIChatMessage::class))
            ->willReturn($updatedRequest->reveal());

        // Mock the tool executor
        $toolResponseMessage = new AIChatMessage(
            AIChatMessageRoleEnum::TOOL,
            'Tool response',
        );

        $this->toolExecutor->execute(Argument::type(AIChatStreamedRequest::class), $toolCall)
            ->willReturn($toolResponseMessage);

        // Mock the updated request with tool response
        $finalRequest = $this->prophesize(AIChatStreamedRequest::class);
        $finalRequest->getMetadata()->willReturn(['key' => 'final']);
        $updatedRequest->withMessage($toolResponseMessage)->willReturn($finalRequest->reveal());

        // Create second stream for next middleware response
        $secondMessage = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'Response after tool execution',
            null,
        );

        $secondStreamMessages = [$secondMessage];
        $secondStreamIterator = new \ArrayIterator($secondStreamMessages);

        $usageTracker2 = new StreamingUsageTracker();
        $usageTracker2->updateUsage(new Usage(5, 10, 15), true);
        $secondStream = new AIChatResponseStream(
            $finalRequest->reveal(),
            $secondStreamIterator,
            [],
            $usageTracker2,
        );

        $nextMiddleware = function ($request, $adapter) use ($finalRequest, $secondStream) {
            $this->assertSame($finalRequest->reveal(), $request);

            return $secondStream;
        };

        $decorator = new ToolStreamResponseDecorator(
            $originalStream,
            $this->request->reveal(),
            $this->adapter->reveal(),
            $nextMiddleware,
            $this->toolExecutor->reveal(),
            self::MAX_TOOL_EXECUTIONS,
        );

        $resultMessages = \iterator_to_array($decorator->getMessageStream());

        // We should have the original 2 messages plus 1 message from the second stream
        $this->assertCount(3, $resultMessages);
        $this->assertSame($message1, $resultMessages[0]);
        $this->assertSame($message2, $resultMessages[1]);
        $this->assertSame($secondMessage, $resultMessages[2]);

        // Check accumulated usage
        $this->assertSame(45, $decorator->getUsage()?->totalTokens);
    }

    public function testGetMessageStreamWithNestedToolCalls(): void
    {
        // Mock getTools() to include both tools
        $this->request->getTools()->willReturn([
            'first_tool' => [(object) [], 'method1'],
            'second_tool' => [(object) [], 'method2'],
        ]);

        // Create two tool calls
        $toolCall1 = new AIChatToolCall(
            ToolTypeEnum::FUNCTION,
            'id_123',
            'first_tool',
            ['param' => 'value1'],
        );

        $toolCall2 = new AIChatToolCall(
            ToolTypeEnum::FUNCTION,
            'id_456',
            'second_tool',
            ['param' => 'value2'],
        );

        // First stream has a message with a tool call
        $message1 = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'First message with tool call',
            [$toolCall1],
        );

        // Create original stream
        $messageIterator = new \ArrayIterator([$message1]);
        $usageTracker1 = new StreamingUsageTracker();
        $usageTracker1->updateUsage(new Usage(10, 20, 30), true);
        $originalStream = new AIChatResponseStream(
            $this->request->reveal(),
            $messageIterator,
            [],
            $usageTracker1,
        );

        // Mock the request handling for first call
        $updatedRequest1 = $this->prophesize(AIChatStreamedRequest::class);
        $updatedRequest1->getTools()->willReturn([
            'first_tool' => [(object) [], 'method1'],
            'second_tool' => [(object) [], 'method2'],
        ]);
        $this->request->withMessage(Argument::type(AIChatMessage::class))
            ->willReturn($updatedRequest1->reveal());

        // Mock the tool executor response for first call
        $toolResponseMessage1 = new AIChatMessage(
            AIChatMessageRoleEnum::TOOL,
            'First tool response',
        );

        $this->toolExecutor->execute(Argument::type(AIChatStreamedRequest::class), $toolCall1)
            ->willReturn($toolResponseMessage1);

        $finalRequest1 = $this->prophesize(AIChatStreamedRequest::class);
        $finalRequest1->getTools()->willReturn([
            'first_tool' => [(object) [], 'method1'],
            'second_tool' => [(object) [], 'method2'],
        ]);
        $updatedRequest1->withMessage($toolResponseMessage1)->willReturn($finalRequest1->reveal());
        $finalRequest1->getMetadata()->willReturn(['key' => 'final1']);

        // Second stream has a message with another tool call
        $secondStreamMessage = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'Second message with tool call',
            [$toolCall2],
        );

        $secondStreamIterator = new \ArrayIterator([$secondStreamMessage]);
        $usageTracker2 = new StreamingUsageTracker();
        $usageTracker2->updateUsage(new Usage(5, 10, 15), true);
        $secondStream = new AIChatResponseStream(
            $finalRequest1->reveal(),
            $secondStreamIterator,
            [],
            $usageTracker2,
        );

        // Mock the request handling for second call
        $updatedRequest2 = $this->prophesize(AIChatStreamedRequest::class);
        $updatedRequest2->getTools()->willReturn([
            'first_tool' => [(object) [], 'method1'],
            'second_tool' => [(object) [], 'method2'],
        ]);
        $finalRequest1->withMessage(Argument::type(AIChatMessage::class))
            ->willReturn($updatedRequest2->reveal());

        // Mock the tool executor response for second call
        $toolResponseMessage2 = new AIChatMessage(
            AIChatMessageRoleEnum::TOOL,
            'Second tool response',
        );

        $this->toolExecutor->execute(Argument::type(AIChatStreamedRequest::class), $toolCall2)
            ->willReturn($toolResponseMessage2);

        $finalRequest2 = $this->prophesize(AIChatStreamedRequest::class);
        $updatedRequest2->withMessage($toolResponseMessage2)->willReturn($finalRequest2->reveal());
        $finalRequest2->getMetadata()->willReturn(['key' => 'final2']);

        // Third stream has no tool calls
        $thirdStreamMessage = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'Final message with no tool calls',
            null,
        );

        $thirdStreamIterator = new \ArrayIterator([$thirdStreamMessage]);
        $usageTracker3 = new StreamingUsageTracker();
        $usageTracker3->updateUsage(new Usage(2, 3, 4), true);
        $thirdStream = new AIChatResponseStream(
            $finalRequest2->reveal(),
            $thirdStreamIterator,
            [],
            $usageTracker3,
        );

        // Setup the next middleware to return the second and third streams in sequence
        $nextCalls = 0;
        $nextMiddleware = function ($request, $adapter) use (&$nextCalls, $finalRequest1, $finalRequest2, $secondStream, $thirdStream) {
            ++$nextCalls;
            if (1 === $nextCalls) {
                $this->assertSame($finalRequest1->reveal(), $request);

                return $secondStream;
            }
            $this->assertSame($finalRequest2->reveal(), $request);

            return $thirdStream;
        };

        $decorator = new ToolStreamResponseDecorator(
            $originalStream,
            $this->request->reveal(),
            $this->adapter->reveal(),
            $nextMiddleware,
            $this->toolExecutor->reveal(),
            self::MAX_TOOL_EXECUTIONS,
        );

        $resultMessages = \iterator_to_array($decorator->getMessageStream());

        // We should have 1 message from original + 1 from second + 1 from third = 3 messages
        $this->assertCount(3, $resultMessages);
        $this->assertSame($message1, $resultMessages[0]);
        $this->assertSame($secondStreamMessage, $resultMessages[1]);
        $this->assertSame($thirdStreamMessage, $resultMessages[2]);

        // Check accumulated usage (original + second + third)
        $this->assertSame(49, $decorator->getUsage()?->totalTokens);
    }

    public function testGetMessageStreamRespectsMaxExecutions(): void
    {
        // Mock getTools() to include the tool
        $this->request->getTools()->willReturn(['recursive_tool' => [(object) [], 'method']]);

        // Create a tool call that will be used in a loop
        $toolCall = new AIChatToolCall(
            ToolTypeEnum::FUNCTION,
            'id_123',
            'recursive_tool',
            ['param' => 'value'],
        );

        // First stream has a message with a tool call
        $message = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'Message with tool call',
            [$toolCall],
        );

        // Create original stream
        $messageIterator = new \ArrayIterator([$message]);
        $usageTracker = new StreamingUsageTracker();
        $usageTracker->updateUsage(new Usage(10, 20, 30), true);
        $originalStream = new AIChatResponseStream(
            $this->request->reveal(),
            $messageIterator,
            [],
            $usageTracker,
        );

        // Mock the request handling
        $updatedRequest = $this->prophesize(AIChatStreamedRequest::class);
        $updatedRequest->getTools()->willReturn(['recursive_tool' => [(object) [], 'method']]);
        $this->request->withMessage(Argument::type(AIChatMessage::class))
            ->willReturn($updatedRequest->reveal());

        // Mock the tool executor
        $toolResponseMessage = new AIChatMessage(
            AIChatMessageRoleEnum::TOOL,
            'Tool response',
        );

        $this->toolExecutor->execute(Argument::any(), Argument::any())
            ->willReturn($toolResponseMessage);

        $finalRequest = $this->prophesize(AIChatStreamedRequest::class);
        $finalRequest->getTools()->willReturn(['recursive_tool' => [(object) [], 'method']]);
        $updatedRequest->withMessage(Argument::any())->willReturn($finalRequest->reveal());
        $finalRequest->withMessage(Argument::any())->willReturn($finalRequest->reveal());
        $finalRequest->getMetadata()->willReturn(['key' => 'final']);

        // Setup a stream that will always return a message with a tool call
        // This would cause infinite recursion if not for maxExecutions
        $nextStreamMessage = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'Recursive message with tool call',
            [$toolCall],
        );

        // Create a stream that always returns the same message with a tool call
        $nextStreamIterator = new \ArrayIterator([$nextStreamMessage]);
        $nextUsageTracker = new StreamingUsageTracker();
        $nextUsageTracker->updateUsage(new Usage(1, 1, 1), true);
        $nextStream = new AIChatResponseStream(
            $finalRequest->reveal(),
            $nextStreamIterator,
            [],
            $nextUsageTracker,
        );

        // The next middleware always returns the same stream with a tool call
        $nextMiddleware = function ($request, $adapter) use ($nextStream) {
            return $nextStream;
        };

        // Create decorator with MAX_TOOL_EXECUTIONS set to 3
        $decorator = new ToolStreamResponseDecorator(
            $originalStream,
            $this->request->reveal(),
            $this->adapter->reveal(),
            $nextMiddleware,
            $this->toolExecutor->reveal(),
            3, // MAX_TOOL_EXECUTIONS
        );

        // Should only process MAX_TOOL_EXECUTIONS + 1 streams (original + 3 tool calls)
        $resultMessages = \iterator_to_array($decorator->getMessageStream());

        // 1 original message + 3 recursive messages = 4 messages total
        $this->assertCount(4, $resultMessages);

        // Check accumulated usage (original + 3 next streams)
        $this->assertSame(33, $decorator->getUsage()?->totalTokens);
    }

    public function testDefaultUsageWhenOriginalStreamHasNullUsage(): void
    {
        // Create a message for the stream
        $message = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'Hello',
            null,
        );

        // Mock original stream with null usage
        $originalStream = $this->prophesize(AIChatResponseStreamInterface::class);
        $originalStream->getMessageStream()->willReturn(new \ArrayIterator([$message]));
        $originalStream->getMessage()->willReturn($message);
        $originalStream->getUsage()->willReturn(null);

        $nextMiddleware = function ($request, $adapter) use ($originalStream) {
            return $originalStream->reveal();
        };

        $decorator = new ToolStreamResponseDecorator(
            $originalStream->reveal(),
            $this->request->reveal(),
            $this->adapter->reveal(),
            $nextMiddleware,
            $this->toolExecutor->reveal(),
            self::MAX_TOOL_EXECUTIONS,
        );

        $this->assertNull($decorator->getUsage());

        // Should still return messages correctly
        $resultMessages = \iterator_to_array($decorator->getMessageStream());
        $this->assertCount(1, $resultMessages);
    }

    public function testGetMessageStreamPreventsInfiniteLoopWhenAllToolCallsAreInvalid(): void
    {
        // Create tool calls for non-existent tools
        $invalidToolCall1 = new AIChatToolCall(
            ToolTypeEnum::FUNCTION,
            'id_123',
            'non_existent_tool_1',
            ['param' => 'value1'],
        );

        $invalidToolCall2 = new AIChatToolCall(
            ToolTypeEnum::FUNCTION,
            'id_456',
            'non_existent_tool_2',
            ['param' => 'value2'],
        );

        // Create message with invalid tool calls
        $message = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'Hello with invalid tools',
            [$invalidToolCall1, $invalidToolCall2],
        );

        // Mock getTools() to return empty array (no tools available)
        $this->request->getTools()->willReturn([]);

        // Create original stream
        $messageIterator = new \ArrayIterator([$message]);
        $usageTracker = new StreamingUsageTracker();
        $usageTracker->updateUsage(new Usage(10, 20, 30), true);
        $originalStream = new AIChatResponseStream(
            $this->request->reveal(),
            $messageIterator,
            [],
            $usageTracker,
        );

        // Mock the request handling - should still be called once to add the assistant message
        $updatedRequest = $this->prophesize(AIChatStreamedRequest::class);
        $updatedRequest->getMetadata()->willReturn(['key' => 'updated']);
        $updatedRequest->getTools()->willReturn([]);

        $this->request->withMessage(Argument::type(AIChatMessage::class))
            ->willReturn($updatedRequest->reveal());

        // Tool executor should NEVER be called since all tools are invalid
        $this->toolExecutor->execute(Argument::cetera())
            ->shouldNotBeCalled();

        // Next middleware should also NEVER be called because we return early
        $middlewareCalled = false;
        $nextMiddleware = function () use (&$middlewareCalled) {
            $middlewareCalled = true;

            $message = new AIChatResponseMessage(
                AIChatMessageRoleEnum::ASSISTANT,
                'Should not be called',
                null,
            );

            $usageTracker = new StreamingUsageTracker();
            $usageTracker->updateUsage(Usage::empty(), true);

            return new AIChatResponseStream(
                $this->request->reveal(),
                new \ArrayIterator([$message]),
                [],
                $usageTracker,
            );
        };

        $decorator = new ToolStreamResponseDecorator(
            $originalStream,
            $this->request->reveal(),
            $this->adapter->reveal(),
            $nextMiddleware,
            $this->toolExecutor->reveal(),
            self::MAX_TOOL_EXECUTIONS,
        );

        $resultMessages = \iterator_to_array($decorator->getMessageStream());

        // Should only return the original message, no nested stream processing
        $this->assertCount(1, $resultMessages);
        $this->assertSame($message, $resultMessages[0]);
        $this->assertFalse($middlewareCalled, 'Next middleware should not be called when all tool calls are invalid');

        // Should only have original usage, no accumulated usage from nested streams
        $this->assertSame(30, $decorator->getUsage()?->totalTokens);
    }
}
