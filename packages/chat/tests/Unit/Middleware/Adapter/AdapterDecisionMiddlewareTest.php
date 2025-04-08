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
use ModelflowAi\Chat\Middleware\Adapter\AdapterDecisionMiddleware;
use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\Chat\Response\AIChatResponseInterface;
use ModelflowAi\DecisionTree\DecisionTreeInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class AdapterDecisionMiddlewareTest extends TestCase
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

    /**
     * @var ObjectProphecy<DecisionTreeInterface<AIChatRequest, AIChatAdapterInterface>>
     */
    private ObjectProphecy $decisionTree;

    private AdapterDecisionMiddleware $middleware;

    protected function setUp(): void
    {
        $this->request = $this->prophesize(AIChatRequest::class);
        $this->adapter = $this->prophesize(AIChatAdapterInterface::class);
        $this->response = $this->prophesize(AIChatResponseInterface::class);

        /** @var ObjectProphecy<DecisionTreeInterface<AIChatRequest, AIChatAdapterInterface>> $decisionTree */
        $decisionTree = $this->prophesize(DecisionTreeInterface::class);
        $this->decisionTree = $decisionTree;

        $this->middleware = new AdapterDecisionMiddleware($this->decisionTree->reveal());
    }

    public function testProcess(): void
    {
        // The decision tree should determine the adapter for the request
        $this->decisionTree->determineAdapter($this->request->reveal())
            ->willReturn($this->adapter->reveal());

        // Next middleware should receive the request and the determined adapter
        $next = function (AIChatRequest $request, ?AIChatAdapterInterface $adapter): AIChatResponseInterface {
            $this->assertSame($this->request->reveal(), $request);
            $this->assertSame($this->adapter->reveal(), $adapter);

            return $this->response->reveal();
        };

        $result = $this->middleware->process($this->request->reveal(), null, $next);

        $this->assertSame($this->response->reveal(), $result);
    }

    public function testProcessOverridesExistingAdapter(): void
    {
        // Create a different adapter to be overridden
        $existingAdapter = $this->prophesize(AIChatAdapterInterface::class);

        // The decision tree should determine the adapter for the request
        $this->decisionTree->determineAdapter($this->request->reveal())
            ->willReturn($this->adapter->reveal());

        // Next middleware should receive the request and the determined adapter (not the existing one)
        $next = function (AIChatRequest $request, ?AIChatAdapterInterface $adapter) use ($existingAdapter): AIChatResponseInterface {
            $this->assertSame($this->request->reveal(), $request);
            $this->assertSame($this->adapter->reveal(), $adapter);
            $this->assertNotSame($existingAdapter->reveal(), $adapter);

            return $this->response->reveal();
        };

        $result = $this->middleware->process($this->request->reveal(), $existingAdapter->reveal(), $next);

        $this->assertSame($this->response->reveal(), $result);
    }
}
