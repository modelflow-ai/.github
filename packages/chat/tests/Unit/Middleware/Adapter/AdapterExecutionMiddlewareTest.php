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

namespace ModelflowAi\Chat\Tests\Unit\Middleware\Adapter;

use ModelflowAi\Chat\Adapter\AIChatAdapterInterface;
use ModelflowAi\Chat\Middleware\Adapter\AdapterExecutionMiddleware;
use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\Chat\Response\AIChatResponseInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class AdapterExecutionMiddlewareTest extends TestCase
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
     * @var ObjectProphecy<AIChatResponseInterface>
     */
    private ObjectProphecy $response;

    private AdapterExecutionMiddleware $middleware;

    protected function setUp(): void
    {
        $this->request = $this->prophesize(AIChatRequest::class);
        $this->adapter = $this->prophesize(AIChatAdapterInterface::class);
        $this->response = $this->prophesize(AIChatResponseInterface::class);

        $this->middleware = new AdapterExecutionMiddleware();
    }

    public function testProcess(): void
    {
        // The adapter should handle the request
        $this->adapter->handleRequest($this->request->reveal())
            ->willReturn($this->response->reveal());

        // The next middleware should not be called
        $next = function (AIChatRequest $request, ?AIChatAdapterInterface $adapter): AIChatResponseInterface {
            $this->fail('Next middleware should not be called');
        };

        $result = $this->middleware->process($this->request->reveal(), $this->adapter->reveal(), $next);

        $this->assertSame($this->response->reveal(), $result);
    }

    public function testProcessWithNullAdapter(): void
    {
        // The next middleware should not be called
        $next = function (AIChatRequest $request, ?AIChatAdapterInterface $adapter): AIChatResponseInterface {
            $this->fail('Next middleware should not be called');
        };

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Adapter is null. Make sure AdapterDecisionMiddleware is called before this middleware.');

        $this->middleware->process($this->request->reveal(), null, $next);
    }

    public function testHandlesRequestWithAdapter(): void
    {
        // Setup a concrete implementation of AIChatAdapterInterface
        $concreteAdapter = new class implements AIChatAdapterInterface {
            private AIChatResponseInterface $response;
            private AIChatRequest $receivedRequest;

            public function setResponse(AIChatResponseInterface $response): void
            {
                $this->response = $response;
            }

            public function getReceivedRequest(): AIChatRequest
            {
                return $this->receivedRequest;
            }

            public function handleRequest(AIChatRequest $request): AIChatResponseInterface
            {
                $this->receivedRequest = $request;

                return $this->response;
            }

            public function supports(object $request): bool
            {
                return true;
            }
        };

        $concreteAdapter->setResponse($this->response->reveal());

        $result = $this->middleware->process($this->request->reveal(), $concreteAdapter, function (): never {
            $this->fail('Next middleware should not be called');
        });

        $this->assertSame($this->response->reveal(), $result);
        $this->assertSame($this->request->reveal(), $concreteAdapter->getReceivedRequest());
    }
}
