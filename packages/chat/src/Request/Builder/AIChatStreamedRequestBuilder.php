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

namespace ModelflowAi\Chat\Request\Builder;

use ModelflowAi\Chat\Request\AIChatMessageCollection;
use ModelflowAi\Chat\Request\AIChatStreamedRequest;
use ModelflowAi\Chat\Response\AIChatResponseStreamInterface;
use ModelflowAi\Chat\ToolInfo\ToolChoiceEnum;

class AIChatStreamedRequestBuilder extends AIChatRequestBuilder
{
    /**
     * @deprecated use AIChatStreamedRequestBuilder::execute instead
     */
    public function build(): AIChatStreamedRequest
    {
        trigger_deprecation('modelflow-ai/chat', '0.3.0', 'Use AIChatStreamedRequestBuilder::execute instead.');

        return $this->doBuild();
    }

    private function doBuild(): AIChatStreamedRequest
    {
        $toolChoice = $this->options['toolChoice'] ?? ToolChoiceEnum::AUTO;
        $responseFormat = $this->options['responseFormat'] ?? null;

        $options = $this->options;
        unset($options['toolChoice'], $options['responseFormat'], $options['streamed']);

        return new AIChatStreamedRequest(
            new AIChatMessageCollection(...$this->messages),
            $this->criteria,
            $this->tools,
            $this->buildToolInfos(),
            $options,
            $this->requestHandler,
            $this->metadata,
            $responseFormat,
            $toolChoice,
        );
    }

    public function execute(): AIChatResponseStreamInterface
    {
        return $this->doBuild()->execute();
    }
}
