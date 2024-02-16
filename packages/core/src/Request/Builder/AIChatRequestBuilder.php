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

namespace ModelflowAi\Core\Request\Builder;

use ModelflowAi\Core\Request\AIChatMessageCollection;
use ModelflowAi\Core\Request\AIChatRequest;
use ModelflowAi\Core\Request\Message\AIChatMessage;
use ModelflowAi\Core\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Core\Request\Message\MessagePart;

class AIChatRequestBuilder extends AIRequestBuilder
{
    /**
     * @var AIChatMessage[]
     */
    protected array $messages = [];

    public static function create(callable $requestHandler): self
    {
        return new self($requestHandler);
    }

    public function addMessage(AIChatMessage $message): self
    {
        $this->messages[] = $message;

        return $this;
    }

    /**
     * @param AIChatMessage[] $messages
     */
    public function addMessages(array $messages): self
    {
        foreach ($messages as $message) {
            $this->addMessage($message);
        }

        return $this;
    }

    /**
     * @param MessagePart[]|MessagePart|string $content
     */
    public function addSystemMessage(array|MessagePart|string $content): self
    {
        $this->messages[] = new AIChatMessage(AIChatMessageRoleEnum::SYSTEM, $content);

        return $this;
    }

    /**
     * @param MessagePart[]|MessagePart|string $content
     */
    public function addAssistantMessage(array|MessagePart|string $content): self
    {
        $this->messages[] = new AIChatMessage(AIChatMessageRoleEnum::ASSISTANT, $content);

        return $this;
    }

    /**
     * @param MessagePart[]|MessagePart|string $content
     */
    public function addUserMessage(array|MessagePart|string $content): self
    {
        $this->messages[] = new AIChatMessage(AIChatMessageRoleEnum::USER, $content);

        return $this;
    }

    public function build(): AIChatRequest
    {
        return new AIChatRequest(
            new AIChatMessageCollection(...$this->messages),
            $this->criteria,
            $this->options,
            $this->requestHandler,
        );
    }
}
