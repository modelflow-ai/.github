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

readonly class AIChatResponse implements AIChatResponseInterface
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private AIChatRequest $request,
        private AIChatResponseMessage $message,
        private ?Usage $usage,
        private array $metadata = [],
    ) {
    }

    public function getRequest(): AIChatRequest
    {
        return $this->request;
    }

    public function getMessage(): AIChatResponseMessage
    {
        return $this->message;
    }

    public function getUsage(): ?Usage
    {
        return $this->usage;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }
}
