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

namespace ModelflowAi\Chat\Middleware;

use ModelflowAi\Chat\Adapter\AIChatAdapterInterface;
use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\Chat\Response\AIChatResponseInterface;

interface AIChatMiddlewareInterface
{
    /**
     * Process the request and optionally modify it before continuing to the next middleware.
     */
    public function process(AIChatRequest $request, ?AIChatAdapterInterface $adapter, callable $next): AIChatResponseInterface;
}
