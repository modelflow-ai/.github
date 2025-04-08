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

use ModelflowAi\Chat\Request\AIChatRequest;

interface AIChatResponseInterface
{
    public function getRequest(): AIChatRequest;

    public function getMessage(): AIChatResponseMessage;

    public function getUsage(): ?Usage;

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array;
}
