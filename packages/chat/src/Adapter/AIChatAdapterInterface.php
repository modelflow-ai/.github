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

namespace ModelflowAi\Chat\Adapter;

use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\Chat\Response\AIChatResponseInterface;
use ModelflowAi\DecisionTree\Behaviour\SupportsBehaviour;

interface AIChatAdapterInterface extends SupportsBehaviour
{
    public function handleRequest(AIChatRequest $request): AIChatResponseInterface;
}
