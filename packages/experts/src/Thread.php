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

use ModelflowAi\Core\AIRequestHandlerInterface;
use ModelflowAi\Core\Response\AIChatResponse;

class Thread implements ThreadInterface
{
    private array $context = [];

    public function __construct(
        private readonly AIRequestHandlerInterface $requestHandler,
        private readonly Expert $expert,
    ) {
    }

    public function addContext(string $key, string $data): self
    {
        $this->context[$key] = $data;

        return $this;
    }

    public function run(): AIChatResponse
    {
        $builder = $this->requestHandler->createChatRequest()
            ->addSystemMessage($this->expert->instructions)
            ->addCriteria($this->expert->criteria)
            ->asJson();

        if ($this->expert->responseFormat instanceof ResponseFormat\ResponseFormatInterface) {
            $builder->addSystemMessage($this->expert->responseFormat->format());
        }

        if ([] !== $this->context) {
            $builder->addUserMessage('Context: ' . \json_encode($this->context));
        }

        return $builder->build()
            ->execute();
    }
}
