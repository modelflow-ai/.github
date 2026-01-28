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
use ModelflowAi\Chat\Middleware\Tools\ToolResponseDecorator;
use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\Chat\Request\Message\AIChatMessage;
use ModelflowAi\Chat\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Chat\Response\AIChatResponse;
use ModelflowAi\Chat\Response\AIChatResponseMessage;
use ModelflowAi\Chat\Response\AIChatToolCall;
use ModelflowAi\Chat\Response\Usage;
use ModelflowAi\Chat\ToolInfo\ToolExecutor;
use ModelflowAi\Chat\ToolInfo\ToolTypeEnum;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ToolResponseDecoratorTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<AIChatRequest>
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

    /**
     * @var callable
     */
    private $nextMiddleware;

    private const MAX_TOOL_EXECUTIONS = 3;

    protected function setUp(): void
    {
        $this->request = $this->prophesize(AIChatRequest::class);
        $this->adapter = $this->prophesize(AIChatAdapterInterface::class);
        $this->toolExecutor = $this->prophesize(ToolExecutor::class);

        $this->request->getMetadata()->willReturn(['key' => 'value']);
        $this->request->getTools()->willReturn([]);

        $this->nextMiddleware = static function ($request, $adapter) {
            return new AIChatResponse(
                $request,
                new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Hello', null),
                Usage::empty(),
            );
        };
    }

    public function testGetMessageReturnsOriginalMessage(): void
    {
        $response = new AIChatResponse(
            $this->request->reveal(),
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Hello', null),
            Usage::empty(),
        );

        $decorator = new ToolResponseDecorator(
            $response,
            $this->request->reveal(),
            $this->adapter->reveal(),
            $this->nextMiddleware,
            $this->toolExecutor->reveal(),
            self::MAX_TOOL_EXECUTIONS,
        );

        $this->assertSame('Hello', $decorator->getMessage()->content);
    }

    public function testGetRequestReturnsRequest(): void
    {
        $response = new AIChatResponse(
            $this->request->reveal(),
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Hello', null),
            Usage::empty(),
        );

        $decorator = new ToolResponseDecorator(
            $response,
            $this->request->reveal(),
            $this->adapter->reveal(),
            $this->nextMiddleware,
            $this->toolExecutor->reveal(),
            self::MAX_TOOL_EXECUTIONS,
        );

        $this->assertSame($this->request->reveal(), $decorator->getRequest());
    }

    public function testGetUsageReturnsAccumulatedUsage(): void
    {
        $usage = new Usage(5, 10, 15);
        $response = new AIChatResponse(
            $this->request->reveal(),
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Hello', null),
            $usage,
        );

        $decorator = new ToolResponseDecorator(
            $response,
            $this->request->reveal(),
            $this->adapter->reveal(),
            $this->nextMiddleware,
            $this->toolExecutor->reveal(),
            self::MAX_TOOL_EXECUTIONS,
        );

        $this->assertSame($usage, $decorator->getUsage());
    }

    public function testGetMetadataReturnsMetadata(): void
    {
        $response = new AIChatResponse(
            $this->request->reveal(),
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Hello', null),
            Usage::empty(),
            ['key' => 'value'],
        );

        $decorator = new ToolResponseDecorator(
            $response,
            $this->request->reveal(),
            $this->adapter->reveal(),
            $this->nextMiddleware,
            $this->toolExecutor->reveal(),
            self::MAX_TOOL_EXECUTIONS,
        );

        $this->assertSame(['key' => 'value'], $decorator->getMetadata());
    }

    public function testProcessToolCallsWithNoToolCalls(): void
    {
        $response = new AIChatResponse(
            $this->request->reveal(),
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Hello', null),
            Usage::empty(),
        );

        $decorator = new ToolResponseDecorator(
            $response,
            $this->request->reveal(),
            $this->adapter->reveal(),
            $this->nextMiddleware,
            $this->toolExecutor->reveal(),
            self::MAX_TOOL_EXECUTIONS,
        );

        $result = $decorator->processToolCalls();

        $this->assertInstanceOf(ToolResponseDecorator::class, $result);
    }

    public function testProcessToolCallsWithEmptyToolCalls(): void
    {
        $response = new AIChatResponse(
            $this->request->reveal(),
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Hello', []),
            Usage::empty(),
        );

        $decorator = new ToolResponseDecorator(
            $response,
            $this->request->reveal(),
            $this->adapter->reveal(),
            $this->nextMiddleware,
            $this->toolExecutor->reveal(),
            self::MAX_TOOL_EXECUTIONS,
        );

        $result = $decorator->processToolCalls();

        $this->assertInstanceOf(ToolResponseDecorator::class, $result);
    }

    public function testProcessToolCallsWithSingleToolCall(): void
    {
        $toolCall = new AIChatToolCall(
            ToolTypeEnum::FUNCTION,
            'id_123',
            'tool_name',
            [
                'param1' => 'value1',
                'param2' => 'value2',
            ],
        );

        $response = new AIChatResponse(
            $this->request->reveal(),
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Hello', [$toolCall]),
            new Usage(5, 10, 15),
        );

        // Mock getTools() to include the tool
        $this->request->getTools()->willReturn(['tool_name' => [(object) [], 'method']]);

        // Mock the request handling
        $updatedRequest = $this->prophesize(AIChatRequest::class);
        $updatedRequest->getMetadata()->willReturn(['key' => 'updated']);
        $updatedRequest->getTools()->willReturn(['tool_name' => [(object) [], 'method']]);

        $this->request->withMessage(Argument::type(AIChatMessage::class))
            ->willReturn($updatedRequest->reveal());

        // Mock the tool executor
        $toolResponseMessage = new AIChatMessage(
            AIChatMessageRoleEnum::TOOL,
            'Tool response',
        );
        $this->toolExecutor->execute(Argument::type(AIChatRequest::class), $toolCall)
            ->willReturn($toolResponseMessage);

        // Mock the updated request with tool response
        $finalRequest = $this->prophesize(AIChatRequest::class);
        $finalRequest->getMetadata()->willReturn(['key' => 'final']);
        $updatedRequest->withMessage($toolResponseMessage)->willReturn($finalRequest->reveal());

        // Mock the next middleware response
        $secondResponseMessage = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'Final response',
        );
        $secondResponse = new AIChatResponse(
            $finalRequest->reveal(),
            $secondResponseMessage,
            new Usage(5, 10, 15),
        );

        $nextMiddleware = function ($request, $adapter) use ($finalRequest, $secondResponse) {
            $this->assertSame($finalRequest->reveal(), $request);

            return $secondResponse;
        };

        $decorator = new ToolResponseDecorator(
            $response,
            $this->request->reveal(),
            $this->adapter->reveal(),
            $nextMiddleware,
            $this->toolExecutor->reveal(),
            self::MAX_TOOL_EXECUTIONS,
        );

        $result = $decorator->processToolCalls();

        $this->assertInstanceOf(ToolResponseDecorator::class, $result);

        // Check the accumulated usage
        $this->assertSame(30, $result->getUsage()?->totalTokens);
    }

    public function testProcessToolCallsWithMultipleToolCalls(): void
    {
        // Mock getTools() to include both tools
        $this->request->getTools()->willReturn([
            'tool_name_1' => [(object) [], 'method1'],
            'tool_name_2' => [(object) [], 'method2'],
        ]);

        // Create multiple tool calls
        $toolCall1 = new AIChatToolCall(
            ToolTypeEnum::FUNCTION,
            'id_123',
            'tool_name_1',
            ['param' => 'value1'],
        );

        $toolCall2 = new AIChatToolCall(
            ToolTypeEnum::FUNCTION,
            'id_456',
            'tool_name_2',
            ['param' => 'value2'],
        );

        $response = new AIChatResponse(
            $this->request->reveal(),
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Hello', [$toolCall1, $toolCall2]),
            new Usage(5, 10, 15),
        );

        // Mock the request handling
        $updatedRequest = $this->prophesize(AIChatRequest::class);
        $updatedRequest->getTools()->willReturn([
            'tool_name_1' => [(object) [], 'method1'],
            'tool_name_2' => [(object) [], 'method2'],
        ]);
        $this->request->withMessage(Argument::type(AIChatMessage::class))
            ->willReturn($updatedRequest->reveal());

        // Mock the tool executors
        $toolResponseMessage1 = new AIChatMessage(
            AIChatMessageRoleEnum::TOOL,
            'Tool response 1',
        );

        $toolResponseMessage2 = new AIChatMessage(
            AIChatMessageRoleEnum::TOOL,
            'Tool response 2',
        );

        $this->toolExecutor->execute(Argument::type(AIChatRequest::class), $toolCall1)
            ->willReturn($toolResponseMessage1);

        $updatedRequest2 = $this->prophesize(AIChatRequest::class);
        $updatedRequest2->getTools()->willReturn([
            'tool_name_1' => [(object) [], 'method1'],
            'tool_name_2' => [(object) [], 'method2'],
        ]);
        $updatedRequest->withMessage($toolResponseMessage1)->willReturn($updatedRequest2->reveal());

        $this->toolExecutor->execute(Argument::type(AIChatRequest::class), $toolCall2)
            ->willReturn($toolResponseMessage2);

        $finalRequest = $this->prophesize(AIChatRequest::class);
        $finalRequest->getMetadata()->willReturn(['key' => 'final']);
        $updatedRequest2->withMessage($toolResponseMessage2)->willReturn($finalRequest->reveal());

        // Mock the next middleware response
        $secondResponseMessage = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'Final response',
            null,
        );

        $secondResponse = new AIChatResponse(
            $finalRequest->reveal(),
            $secondResponseMessage,
            new Usage(5, 10, 15),
        );

        $nextMiddleware = function ($request, $adapter) use ($finalRequest, $secondResponse) {
            $this->assertSame($finalRequest->reveal(), $request);

            return $secondResponse;
        };

        $decorator = new ToolResponseDecorator(
            $response,
            $this->request->reveal(),
            $this->adapter->reveal(),
            $nextMiddleware,
            $this->toolExecutor->reveal(),
            self::MAX_TOOL_EXECUTIONS,
        );

        $result = $decorator->processToolCalls();

        $this->assertInstanceOf(ToolResponseDecorator::class, $result);

        // Check the accumulated usage
        $this->assertSame(30, $result->getUsage()?->totalTokens);
    }

    public function testProcessToolCallsRespectsMaxExecutions(): void
    {
        // Mock getTools() to include the tool
        $this->request->getTools()->willReturn(['recursive_tool' => [(object) [], 'method']]);

        // Create a response chain with tool calls that would exceed max executions
        $toolCall = new AIChatToolCall(
            ToolTypeEnum::FUNCTION,
            'id_123',
            'recursive_tool',
            ['param' => 'value'],
        );

        // First response has tool calls
        $response = new AIChatResponse(
            $this->request->reveal(),
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Hello', [$toolCall]),
            new Usage(1, 2, 3),
        );

        // Mock the request handling for first call
        $updatedRequest = $this->prophesize(AIChatRequest::class);
        $updatedRequest->getTools()->willReturn(['recursive_tool' => [(object) [], 'method']]);
        $this->request->withMessage(Argument::type(AIChatMessage::class))
            ->willReturn($updatedRequest->reveal());

        // Mock the tool executor response
        $toolResponseMessage = new AIChatMessage(
            AIChatMessageRoleEnum::TOOL,
            'Tool response',
        );

        $this->toolExecutor->execute(Argument::type(AIChatRequest::class), Argument::any())
            ->willReturn($toolResponseMessage);

        $finalRequest = $this->prophesize(AIChatRequest::class);
        $finalRequest->getMetadata()->willReturn(['key' => 'final']);
        $finalRequest->getTools()->willReturn(['recursive_tool' => [(object) [], 'method']]);
        $updatedRequest->withMessage($toolResponseMessage)->willReturn($finalRequest->reveal());
        $finalRequest->withMessage(Argument::any())->willReturn($finalRequest->reveal());

        // Create a chain of responses each with tool calls to force max execution check
        $secondResponseMessage = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'Second response',
            [$toolCall],
        );

        $secondResponse = new AIChatResponse(
            $finalRequest->reveal(),
            $secondResponseMessage,
            new Usage(5, 10, 15),
        );

        $thirdResponseMessage = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'Third response',
            [$toolCall],
        );

        $thirdResponse = new AIChatResponse(
            $finalRequest->reveal(),
            $thirdResponseMessage,
            new Usage(2, 3, 4),
        );

        $fourthResponseMessage = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'Fourth response',
            null,
        );

        $fourthResponse = new AIChatResponse(
            $finalRequest->reveal(),
            $fourthResponseMessage,
            new Usage(1, 1, 1),
        );

        // Mock a middleware that returns the chain of responses
        $responseQueue = [$secondResponse, $thirdResponse, $fourthResponse];
        $nextMiddleware = static function ($request, $adapter) use (&$responseQueue) {
            return \array_shift($responseQueue);
        };

        // Create the decorator with max executions of 3
        $decorator = new ToolResponseDecorator(
            $response,
            $this->request->reveal(),
            $this->adapter->reveal(),
            $nextMiddleware,
            $this->toolExecutor->reveal(),
            3, // MAX_TOOL_EXECUTIONS
        );

        $result = $decorator->processToolCalls();

        // Assert that we got back the fourth response (after 3 executions)
        $this->assertInstanceOf(ToolResponseDecorator::class, $result);

        // Ensure we consumed all responses in our test queue
        $this->assertEmpty($responseQueue);

        // Check the accumulated usage (1,2,3 + 5,10,15 + 2,3,4 + 1,1,1)
        $this->assertSame(23, $result->getUsage()?->totalTokens);
    }

    public function testProcessToolCallsSkipsMetadataOnlyTools(): void
    {
        // Create a tool call for a metadata-only tool
        $metadataToolCall = new AIChatToolCall(
            ToolTypeEnum::FUNCTION,
            'id_456',
            'metadata_only_tool',
            ['query' => 'test'],
        );

        // Create a tool call for an executable tool
        $executableToolCall = new AIChatToolCall(
            ToolTypeEnum::FUNCTION,
            'id_123',
            'executable_tool',
            ['param' => 'value'],
        );

        $response = new AIChatResponse(
            $this->request->reveal(),
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Hello', [$metadataToolCall, $executableToolCall]),
            new Usage(5, 10, 15),
        );

        // Mock getTools() to only return the executable tool
        $this->request->getTools()->willReturn([
            'executable_tool' => [(object) [], 'method'],
        ]);

        // Mock the request handling
        $updatedRequest = $this->prophesize(AIChatRequest::class);
        $updatedRequest->getMetadata()->willReturn(['key' => 'updated']);
        $updatedRequest->getTools()->willReturn([
            'executable_tool' => [(object) [], 'method'],
        ]);

        $this->request->withMessage(Argument::type(AIChatMessage::class))
            ->willReturn($updatedRequest->reveal());

        // Mock the tool executor - should ONLY be called for executable tool
        $toolResponseMessage = new AIChatMessage(
            AIChatMessageRoleEnum::TOOL,
            'Tool response',
        );
        $this->toolExecutor->execute(Argument::type(AIChatRequest::class), $executableToolCall)
            ->shouldBeCalledOnce()
            ->willReturn($toolResponseMessage);

        // Ensure executor is NOT called for metadata-only tool
        $this->toolExecutor->execute(Argument::type(AIChatRequest::class), $metadataToolCall)
            ->shouldNotBeCalled();

        // Mock the updated request with tool response
        $finalRequest = $this->prophesize(AIChatRequest::class);
        $finalRequest->getMetadata()->willReturn(['key' => 'final']);
        $updatedRequest->withMessage($toolResponseMessage)->willReturn($finalRequest->reveal());

        // Mock the next middleware response
        $secondResponse = new AIChatResponse(
            $finalRequest->reveal(),
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Final response'),
            new Usage(5, 10, 15),
        );

        $nextMiddleware = static function () use ($secondResponse) {
            return $secondResponse;
        };

        $decorator = new ToolResponseDecorator(
            $response,
            $this->request->reveal(),
            $this->adapter->reveal(),
            $nextMiddleware,
            $this->toolExecutor->reveal(),
            self::MAX_TOOL_EXECUTIONS,
        );

        $result = $decorator->processToolCalls();

        $this->assertInstanceOf(ToolResponseDecorator::class, $result);
    }

    public function testProcessToolCallsPreventsInfiniteLoopWhenAllToolCallsAreInvalid(): void
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

        $response = new AIChatResponse(
            $this->request->reveal(),
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Hello', [$invalidToolCall1, $invalidToolCall2]),
            new Usage(5, 10, 15),
        );

        // Mock getTools() to return empty array (no tools available)
        $this->request->getTools()->willReturn([]);

        // Mock the request handling - should still be called once to add the assistant message
        $updatedRequest = $this->prophesize(AIChatRequest::class);
        $updatedRequest->getMetadata()->willReturn(['key' => 'updated']);
        $updatedRequest->getTools()->willReturn([]);

        $this->request->withMessage(Argument::type(AIChatMessage::class))
            ->willReturn($updatedRequest->reveal());

        // Tool executor should NEVER be called since all tools are invalid
        $this->toolExecutor->execute(Argument::cetera())
            ->shouldNotBeCalled();

        // Next middleware should also NEVER be called because we break early
        $middlewareCalled = false;
        $nextMiddleware = function () use (&$middlewareCalled) {
            $middlewareCalled = true;

            return new AIChatResponse(
                $this->request->reveal(),
                new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Should not be called'),
                Usage::empty(),
            );
        };

        $decorator = new ToolResponseDecorator(
            $response,
            $this->request->reveal(),
            $this->adapter->reveal(),
            $nextMiddleware,
            $this->toolExecutor->reveal(),
            self::MAX_TOOL_EXECUTIONS,
        );

        $result = $decorator->processToolCalls();

        $this->assertInstanceOf(ToolResponseDecorator::class, $result);
        $this->assertFalse($middlewareCalled, 'Next middleware should not be called when all tool calls are invalid');

        // Should return the original response since no valid tool calls were processed
        $this->assertSame('Hello', $result->getMessage()->content);
    }
}
