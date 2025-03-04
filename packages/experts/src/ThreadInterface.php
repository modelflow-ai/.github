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

namespace ModelflowAi\Experts;

use ModelflowAi\Chat\Request\Message\AIChatMessage;
use ModelflowAi\Chat\Request\Message\MessagePart;
use ModelflowAi\Chat\Response\AIChatResponse;
use ModelflowAi\Chat\Response\AIChatResponseStream;

interface ThreadInterface
{
    public function addContext(string $key, mixed $data): self;

    /**
     * @param array<string, mixed> $metadata
     */
    public function addMetadata(array $metadata): self;

    public function run(): AIChatResponse;

    public function runStreamed(): AIChatResponseStream;

    public function addMessage(AIChatMessage $message): self;

    /**
     * @param MessagePart[]|MessagePart|string $content
     */
    public function addSystemMessage(array|MessagePart|string $content): self;

    /**
     * @param MessagePart[]|MessagePart|string $content
     */
    public function addAssistantMessage(array|MessagePart|string $content): self;

    /**
     * @param MessagePart[]|MessagePart|string $content
     */
    public function addUserMessage(array|MessagePart|string $content): self;

    /**
     * @param AIChatMessage[] $messages
     */
    public function addMessages(array $messages): self;
}
