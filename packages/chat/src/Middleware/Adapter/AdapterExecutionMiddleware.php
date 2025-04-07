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

namespace ModelflowAi\Chat\Middleware\Adapter;

use ModelflowAi\Chat\Adapter\AIChatAdapterInterface;
use ModelflowAi\Chat\Middleware\AIChatMiddlewareInterface;
use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\Chat\Response\AIChatResponseInterface;
use Webmozart\Assert\Assert;

/**
 * Middleware that executes the adapter determined by the AdapterDecisionMiddleware.
 * This middleware should be the last in the chain.
 */
final readonly class AdapterExecutionMiddleware implements AIChatMiddlewareInterface
{
    public function process(AIChatRequest $request, ?AIChatAdapterInterface $adapter, callable $next): AIChatResponseInterface
    {
        Assert::notNull($adapter, 'Adapter is null. Make sure AdapterDecisionMiddleware is called before this middleware.');

        return $adapter->handleRequest($request);
    }
}
