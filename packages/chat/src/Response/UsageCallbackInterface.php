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

namespace ModelflowAi\Chat\Response;

/**
 * Callback interface for receiving usage updates during streaming responses.
 *
 * Implementations of this interface can be registered with AIChatResponseStream
 * to receive real-time usage updates as they become available from the provider.
 */
interface UsageCallbackInterface
{
    public function onUsageUpdate(Usage $usage, bool $isFinal): void;
}
