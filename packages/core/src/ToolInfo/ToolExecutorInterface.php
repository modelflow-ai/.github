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

namespace ModelflowAi\Core\ToolInfo;

use ModelflowAi\Core\Request\AIChatRequest;
use ModelflowAi\Core\Request\Message\AIChatMessage;
use ModelflowAi\Core\Response\AIChatToolCall;

interface ToolExecutorInterface
{
    public function execute(AIChatRequest $request, AIChatToolCall $toolCall): AIChatMessage;
}
