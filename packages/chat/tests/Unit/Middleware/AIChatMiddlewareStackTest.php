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

namespace ModelflowAi\Chat\Tests\Unit\Middleware;

use ModelflowAi\Chat\Adapter\AIChatAdapterInterface;
use ModelflowAi\Chat\Middleware\AIChatMiddlewareInterface;
use ModelflowAi\Chat\Middleware\AIChatMiddlewareStack;
use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\Chat\Response\AIChatResponseInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class AIChatMiddlewareStackTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<AIChatRequest>
     */
    private ObjectProphecy $request;

    /**
     * @var ObjectProphecy<AIChatResponseInterface>
     */
    private ObjectProphecy $response;

    /**
     * @var ObjectProphecy<AIChatAdapterInterface>
     */
    private ObjectProphecy $adapter;

    private AIChatMiddlewareStack $middlewareStack;

    protected function setUp(): void
    {
        $this->request = $this->prophesize(AIChatRequest::class);
        $this->response = $this->prophesize(AIChatResponseInterface::class);
        $this->adapter = $this->prophesize(AIChatAdapterInterface::class);
        $this->middlewareStack = new AIChatMiddlewareStack();
    }

    public function testAddMiddleware(): void
    {
        $middleware = $this->prophesize(AIChatMiddlewareInterface::class);

        $result = $this->middlewareStack->add($middleware->reveal());

        $this->assertSame($this->middlewareStack, $result);
    }

    public function testHandleWithSingleMiddleware(): void
    {
        $middleware = $this->prophesize(AIChatMiddlewareInterface::class);

        $middleware->process(
            $this->request->reveal(),
            null,
            Argument::type('callable'),
        )->willReturn($this->response->reveal());

        $this->middlewareStack->add($middleware->reveal());

        $result = $this->middlewareStack->handle($this->request->reveal());

        $this->assertSame($this->response->reveal(), $result);
    }

    public function testHandleWithMultipleMiddlewares(): void
    {
        $firstMiddleware = $this->prophesize(AIChatMiddlewareInterface::class);
        $secondMiddleware = $this->prophesize(AIChatMiddlewareInterface::class);

        // Setup first middleware to call the next middleware
        $firstMiddleware->process(
            $this->request->reveal(),
            null,
            Argument::type('callable'),
        )->will(fn ($args) => $args[2]($args[0], $args[1]));

        // Setup second middleware to return the response
        $secondMiddleware->process(
            $this->request->reveal(),
            null,
            Argument::type('callable'),
        )->willReturn($this->response->reveal());

        $this->middlewareStack->add($firstMiddleware->reveal());
        $this->middlewareStack->add($secondMiddleware->reveal());

        $result = $this->middlewareStack->handle($this->request->reveal());

        $this->assertSame($this->response->reveal(), $result);
    }

    public function testMiddlewareChainPassingAdapterBetweenMiddlewares(): void
    {
        $firstMiddleware = $this->prophesize(AIChatMiddlewareInterface::class);
        $secondMiddleware = $this->prophesize(AIChatMiddlewareInterface::class);

        $adapter = $this->adapter->reveal();

        // First middleware sets the adapter and calls next
        $firstMiddleware->process(
            $this->request->reveal(),
            null,
            Argument::type('callable'),
        )->will(fn ($args) => $args[2]($args[0], $adapter));

        // Second middleware should receive the adapter from first middleware
        $secondMiddleware->process(
            $this->request->reveal(),
            $this->adapter->reveal(),
            Argument::type('callable'),
        )->willReturn($this->response->reveal());

        $this->middlewareStack->add($firstMiddleware->reveal());
        $this->middlewareStack->add($secondMiddleware->reveal());

        $result = $this->middlewareStack->handle($this->request->reveal());

        $this->assertSame($this->response->reveal(), $result);
    }

    public function testHandleWithMiddlewareModifyingRequest(): void
    {
        $originalRequest = $this->request->reveal();
        $modifiedRequest = $this->prophesize(AIChatRequest::class)->reveal();

        $firstMiddleware = $this->prophesize(AIChatMiddlewareInterface::class);
        $secondMiddleware = $this->prophesize(AIChatMiddlewareInterface::class);

        // First middleware modifies the request and calls next
        $firstMiddleware->process(
            $originalRequest,
            null,
            Argument::type('callable'),
        )->will(fn ($args) => $args[2]($modifiedRequest, $args[1]));

        // Second middleware should receive the modified request
        $secondMiddleware->process(
            $modifiedRequest,
            null,
            Argument::type('callable'),
        )->willReturn($this->response->reveal());

        $this->middlewareStack->add($firstMiddleware->reveal());
        $this->middlewareStack->add($secondMiddleware->reveal());

        $result = $this->middlewareStack->handle($originalRequest);

        $this->assertSame($this->response->reveal(), $result);
    }

    public function testExceptionWhenStackExhaustedWithoutResponse(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Middleware stack exhausted without producing a response. Make sure AdapterExecutionMiddleware is the last middleware in the stack.');

        $middleware = $this->prophesize(AIChatMiddlewareInterface::class);

        // Middleware calls next but doesn't return a response
        $middleware->process(
            $this->request->reveal(),
            null,
            Argument::type('callable'),
        )->will(fn ($args) => $args[2]($args[0], $args[1]));

        $this->middlewareStack->add($middleware->reveal());

        $this->middlewareStack->handle($this->request->reveal());
    }

    public function testMiddlewareHandlesRequestDirectlyWithoutCallingNext(): void
    {
        $middleware = $this->prophesize(AIChatMiddlewareInterface::class);

        // Middleware handles request directly and returns response without calling next
        $middleware->process(
            $this->request->reveal(),
            null,
            Argument::type('callable'),
        )->willReturn($this->response->reveal());

        $this->middlewareStack->add($middleware->reveal());

        $result = $this->middlewareStack->handle($this->request->reveal());

        $this->assertSame($this->response->reveal(), $result);
    }

    public function testMiddlewareChainWithConcreteImplementation(): void
    {
        // Create a concrete implementation of middleware for testing
        $firstMiddleware = new class implements AIChatMiddlewareInterface {
            public function process(AIChatRequest $request, ?AIChatAdapterInterface $adapter, callable $next): AIChatResponseInterface
            {
                // Just pass to next middleware
                return $next($request, $adapter);
            }
        };

        // Create a concrete implementation that returns a response
        $secondMiddleware = new class($this->response->reveal()) implements AIChatMiddlewareInterface {
            public function __construct(private readonly AIChatResponseInterface $response)
            {
            }

            public function process(AIChatRequest $request, ?AIChatAdapterInterface $adapter, callable $next): AIChatResponseInterface
            {
                // Return the response directly
                return $this->response;
            }
        };

        $this->middlewareStack->add($firstMiddleware);
        $this->middlewareStack->add($secondMiddleware);

        $result = $this->middlewareStack->handle($this->request->reveal());

        $this->assertSame($this->response->reveal(), $result);
    }
}
