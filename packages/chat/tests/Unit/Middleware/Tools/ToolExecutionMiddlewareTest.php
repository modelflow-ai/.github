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
use ModelflowAi\Chat\Middleware\Tools\ToolExecutionMiddleware;
use ModelflowAi\Chat\Middleware\Tools\ToolResponseDecorator;
use ModelflowAi\Chat\Middleware\Tools\ToolStreamResponseDecorator;
use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\Chat\Request\AIChatStreamedRequest;
use ModelflowAi\Chat\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Chat\Response\AIChatResponse;
use ModelflowAi\Chat\Response\AIChatResponseMessage;
use ModelflowAi\Chat\Response\AIChatResponseStream;
use ModelflowAi\Chat\Response\Usage;
use ModelflowAi\Chat\ToolInfo\ToolExecutor;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ToolExecutionMiddlewareTest extends TestCase
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

    /**
     * @var ObjectProphecy<AIChatAdapterInterface>
     */
    private ObjectProphecy $adapter;

    /**
     * @var ObjectProphecy<ToolExecutor>
     */
    private ObjectProphecy $toolExecutor;

    private ToolExecutionMiddleware $middleware;

    protected function setUp(): void
    {
        $this->request = $this->prophesize(AIChatRequest::class);
        $this->streamedRequest = $this->prophesize(AIChatStreamedRequest::class);
        $this->adapter = $this->prophesize(AIChatAdapterInterface::class);
        $this->toolExecutor = $this->prophesize(ToolExecutor::class);

        $this->middleware = new ToolExecutionMiddleware($this->toolExecutor->reveal(), 10);
    }

    public function testProcessWithRegularResponse(): void
    {
        // Create a response message with no tool calls
        $responseMessage = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'Hello',
            null,
        );

        // Create a response
        $response = new AIChatResponse(
            $this->request->reveal(),
            $responseMessage,
            Usage::empty(),
        );

        $next = static fn (AIChatRequest $request, ?AIChatAdapterInterface $adapter) => $response;

        $result = $this->middleware->process($this->request->reveal(), $this->adapter->reveal(), $next);

        $this->assertInstanceOf(ToolResponseDecorator::class, $result);
    }

    public function testProcessWithStreamedResponse(): void
    {
        // Create an iterator of messages for the stream
        $messages = [
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Hello', null),
        ];
        $messageIterator = new \ArrayIterator($messages);

        // Create a streamed response
        $streamResponse = new AIChatResponseStream(
            $this->streamedRequest->reveal(),
            $messageIterator,
        );

        $next = static fn (AIChatRequest $request, ?AIChatAdapterInterface $adapter) => $streamResponse;

        $result = $this->middleware->process(
            $this->streamedRequest->reveal(),
            $this->adapter->reveal(),
            $next,
        );

        $this->assertInstanceOf(ToolStreamResponseDecorator::class, $result);
    }

    public function testProcessWithCustomMaxExecutions(): void
    {
        // Create middleware with custom max executions
        $middleware = new ToolExecutionMiddleware($this->toolExecutor->reveal(), 5);

        // Create a response message with no tool calls
        $responseMessage = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'Hello',
            null,
        );

        // Create a response
        $response = new AIChatResponse(
            $this->request->reveal(),
            $responseMessage,
            Usage::empty(),
        );

        $next = static fn (AIChatRequest $request, ?AIChatAdapterInterface $adapter) => $response;

        $result = $middleware->process($this->request->reveal(), $this->adapter->reveal(), $next);

        $this->assertInstanceOf(ToolResponseDecorator::class, $result);
        // We can't directly test the max executions value as it's private,
        // but we've verified the constructor accepts it
    }

    public function testProcessWithDefaultToolExecutor(): void
    {
        // Create middleware with default tool executor
        $middleware = new ToolExecutionMiddleware();

        // Create a response message with no tool calls
        $responseMessage = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            'Hello',
            null,
        );

        // Create a response
        $response = new AIChatResponse(
            $this->request->reveal(),
            $responseMessage,
            Usage::empty(),
        );

        $next = static fn (AIChatRequest $request, ?AIChatAdapterInterface $adapter) => $response;

        $result = $middleware->process($this->request->reveal(), $this->adapter->reveal(), $next);

        $this->assertInstanceOf(ToolResponseDecorator::class, $result);
    }
}
