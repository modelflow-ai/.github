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

namespace ModelflowAi\Chat;

use ModelflowAi\Chat\Adapter\AIChatAdapterInterface;
use ModelflowAi\Chat\Middleware\Adapter\AdapterDecisionMiddleware;
use ModelflowAi\Chat\Middleware\Adapter\AdapterExecutionMiddleware;
use ModelflowAi\Chat\Middleware\AIChatMiddlewareInterface;
use ModelflowAi\Chat\Middleware\AIChatMiddlewareStack;
use ModelflowAi\Chat\Middleware\ResponseFormat\ResponseFormatMiddleware;
use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\Chat\Request\Builder\AIChatRequestBuilder;
use ModelflowAi\Chat\Request\Builder\AIChatStreamedRequestBuilder;
use ModelflowAi\Chat\Request\Message\AIChatMessage;
use ModelflowAi\Chat\Response\AIChatResponseInterface;
use ModelflowAi\Chat\Response\AIChatResponseStreamInterface;
use ModelflowAi\DecisionTree\DecisionTreeInterface;
use Webmozart\Assert\Assert;

final class AIChatRequestHandler implements AIChatRequestHandlerInterface
{
    public static function create(
        DecisionTreeInterface $decisionTree,
        iterable $middleware = [],
    ): self {
        $middleware = [
            new AdapterDecisionMiddleware($decisionTree),
            new ResponseFormatMiddleware(),
            ...$middleware,
            new AdapterExecutionMiddleware(),
        ];

        return new self($middleware);
    }

    private readonly AIChatMiddlewareStack $middlewareStack;

    /**
     * @param AIChatMiddlewareInterface[] $middleware Optional middleware to add
     */
    public function __construct(
        iterable $middleware = [],
    ) {
        $this->middlewareStack = new AIChatMiddlewareStack();

        foreach ($middleware as $m) {
            $this->middlewareStack->add($m);
        }
    }

    public function createRequest(AIChatMessage ...$messages): AIChatRequestBuilder
    {
        return AIChatRequestBuilder::create(function (AIChatRequest $request): AIChatResponseInterface {
            $response = $this->middlewareStack->handle($request);
            Assert::isInstanceOf($response, AIChatResponseInterface::class);

            return $response;
        })->addMessages($messages);
    }

    public function createStreamedRequest(AIChatMessage ...$messages): AIChatStreamedRequestBuilder
    {
        return AIChatStreamedRequestBuilder::create(function (AIChatRequest $request): AIChatResponseStreamInterface {
            $response = $this->middlewareStack->handle($request);
            Assert::isInstanceOf($response, AIChatResponseStreamInterface::class);

            return $response;
        })->addMessages($messages);
    }
}
