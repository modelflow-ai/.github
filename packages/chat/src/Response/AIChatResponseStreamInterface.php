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

use ModelflowAi\Chat\Request\AIChatStreamedRequest;

interface AIChatResponseStreamInterface extends AIChatResponseInterface
{
    public function getRequest(): AIChatStreamedRequest;

    /**
     * @return \Iterator<int, AIChatResponseMessage>
     */
    public function getMessageStream(): \Iterator;

    /**
     * Register a callback to receive usage updates as they become available during streaming.
     *
     * The callback will be invoked:
     * - Immediately if usage data is already available
     * - As usage data arrives during streaming
     * - With isFinal=true when the stream completes
     */
    public function registerUsageCallback(UsageCallbackInterface $callback): void;

    /**
     * Get the accumulated usage data.
     *
     * Returns null if:
     * - The stream hasn't been consumed yet
     * - No usage data has been received from the provider
     *
     * After the stream completes, this will return the final accumulated usage.
     */
    public function getUsage(): ?Usage;
}
