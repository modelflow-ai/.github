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

namespace ModelflowAi\Core;

use ModelflowAi\Core\Request\Builder\AIChatRequestBuilder;
use ModelflowAi\Core\Request\Builder\AITextRequestBuilder;
use ModelflowAi\Core\Request\Message\AIChatMessage;

interface AIRequestHandlerInterface
{
    public function createTextRequest(?string $text = null): AITextRequestBuilder;

    public function createChatRequest(AIChatMessage ...$messages): AIChatRequestBuilder;
}
