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

namespace ModelflowAi\Chat\Tests\Unit\Middleware\ResponseFormat;

use ModelflowAi\Chat\Adapter\AIChatAdapterInterface;
use ModelflowAi\Chat\Middleware\ResponseFormat\ResponseFormatMiddleware;
use ModelflowAi\Chat\Request\AIChatMessageCollection;
use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\Chat\Request\ResponseFormat\ResponseFormatInterface;
use ModelflowAi\Chat\Request\ResponseFormat\SupportsResponseFormatInterface;
use ModelflowAi\Chat\Response\AIChatResponseInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ResponseFormatMiddlewareTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<AIChatRequest>
     */
    private ObjectProphecy $request;

    /**
     * @var ObjectProphecy<AIChatMessageCollection>
     */
    private ObjectProphecy $messageCollection;

    /**
     * @var ObjectProphecy<AIChatAdapterInterface>
     */
    private ObjectProphecy $adapter;

    /**
     * @var ObjectProphecy<AIChatResponseInterface>
     */
    private ObjectProphecy $response;

    private ResponseFormatMiddleware $middleware;

    protected function setUp(): void
    {
        $this->request = $this->prophesize(AIChatRequest::class);
        $this->messageCollection = $this->prophesize(AIChatMessageCollection::class);
        $this->adapter = $this->prophesize(AIChatAdapterInterface::class);
        $this->response = $this->prophesize(AIChatResponseInterface::class);

        $this->middleware = new ResponseFormatMiddleware();
    }

    public function testProcessWithNoResponseFormat(): void
    {
        // Request has no response format
        $this->request->getResponseFormat()->willReturn(null);

        // Next middleware should be called with unchanged request and adapter
        $next = function (AIChatRequest $request, ?AIChatAdapterInterface $adapter): AIChatResponseInterface {
            $this->assertSame($this->request->reveal(), $request);
            $this->assertSame($this->adapter->reveal(), $adapter);

            return $this->response->reveal();
        };

        $result = $this->middleware->process($this->request->reveal(), $this->adapter->reveal(), $next);

        $this->assertSame($this->response->reveal(), $result);
    }

    public function testProcessWithSupportedResponseFormat(): void
    {
        // Create a response format
        $responseFormat = $this->prophesize(ResponseFormatInterface::class);

        // Request has a response format
        $this->request->getResponseFormat()->willReturn($responseFormat->reveal());

        // Create an adapter that implements SupportsResponseFormatInterface
        $supportingAdapter = $this->prophesize(AIChatAdapterInterface::class)
            ->willImplement(SupportsResponseFormatInterface::class);

        // Adapter supports the response format
        $supportingAdapter->supportsResponseFormat($responseFormat->reveal())->willReturn(true);

        // Messages collection should NOT be modified
        $this->request->getMessages()->shouldNotBeCalled();

        // Next middleware should be called with unchanged request and adapter
        $next = function (AIChatRequest $request, ?AIChatAdapterInterface $adapter): AIChatResponseInterface {
            $this->assertSame($this->request->reveal(), $request);
            $this->assertSame($adapter, $adapter);

            return $this->response->reveal();
        };

        $result = $this->middleware->process($this->request->reveal(), $supportingAdapter->reveal(), $next);

        $this->assertSame($this->response->reveal(), $result);
    }

    public function testProcessWithUnsupportedResponseFormat(): void
    {
        // Create a response format
        $responseFormat = $this->prophesize(ResponseFormatInterface::class);

        // Request has a response format
        $this->request->getResponseFormat()->willReturn($responseFormat->reveal());
        $this->request->getMessages()->willReturn($this->messageCollection->reveal());

        // Create an adapter that implements SupportsResponseFormatInterface
        $unsupportingAdapter = $this->prophesize(AIChatAdapterInterface::class)
            ->willImplement(SupportsResponseFormatInterface::class);

        // Adapter does NOT support the response format
        $unsupportingAdapter->supportsResponseFormat($responseFormat->reveal())->willReturn(false);

        // Messages collection should be modified to add response format
        $this->messageCollection->addResponseFormat($responseFormat->reveal())->shouldBeCalled();

        // Next middleware should be called
        $next = fn (AIChatRequest $request, ?AIChatAdapterInterface $adapter): AIChatResponseInterface => $this->response->reveal();

        $result = $this->middleware->process($this->request->reveal(), $unsupportingAdapter->reveal(), $next);

        $this->assertSame($this->response->reveal(), $result);
    }

    public function testProcessWithNonSupportingAdapter(): void
    {
        // Create a response format
        $responseFormat = $this->prophesize(ResponseFormatInterface::class);

        // Request has a response format
        $this->request->getResponseFormat()->willReturn($responseFormat->reveal());
        $this->request->getMessages()->willReturn($this->messageCollection->reveal());

        // Standard adapter doesn't implement SupportsResponseFormatInterface

        // Messages collection should be modified to add response format
        $this->messageCollection->addResponseFormat($responseFormat->reveal())->shouldBeCalled();

        // Next middleware should be called
        $next = fn (AIChatRequest $request, ?AIChatAdapterInterface $adapter): AIChatResponseInterface => $this->response->reveal();

        $result = $this->middleware->process($this->request->reveal(), $this->adapter->reveal(), $next);

        $this->assertSame($this->response->reveal(), $result);
    }

    public function testProcessWithNullAdapter(): void
    {
        // Next middleware should be called with unchanged request and null adapter
        $next = function (AIChatRequest $request, ?AIChatAdapterInterface $adapter): AIChatResponseInterface {
            $this->assertSame($this->request->reveal(), $request);
            $this->assertNull($adapter);

            return $this->response->reveal();
        };

        $result = $this->middleware->process($this->request->reveal(), null, $next);

        $this->assertSame($this->response->reveal(), $result);
    }
}
