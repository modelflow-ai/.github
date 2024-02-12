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

use ModelflowAi\Core\DecisionTree\AIModelDecisionTreeInterface;
use ModelflowAi\Core\Request\AIChatRequest;
use ModelflowAi\Core\Request\AIRequestInterface;
use ModelflowAi\Core\Request\AITextRequest;
use ModelflowAi\Core\Request\Builder\AIChatRequestBuilder;
use ModelflowAi\Core\Request\Builder\AITextRequestBuilder;
use ModelflowAi\Core\Request\Message\AIChatMessage;
use ModelflowAi\Core\Response\AIChatResponse;
use ModelflowAi\Core\Response\AIResponseInterface;
use ModelflowAi\Core\Response\AITextResponse;
use Webmozart\Assert\Assert;

class AIRequestHandler implements AIRequestHandlerInterface
{
    public function __construct(
        private readonly AIModelDecisionTreeInterface $decisionTree,
    ) {
    }

    private function handle(AIRequestInterface $request): AIResponseInterface
    {
        $adapter = $this->decisionTree->determineAdapter($request);

        return $adapter->handleRequest($request);
    }

    public function createTextRequest(?string $text = null): AITextRequestBuilder
    {
        return AITextRequestBuilder::create(function (AITextRequest $request): AITextResponse {
            $response = $this->handle($request);
            Assert::isInstanceOf($response, AITextResponse::class);

            return $response;
        })->text($text);
    }

    public function createChatRequest(AIChatMessage ...$messages): AIChatRequestBuilder
    {
        return AIChatRequestBuilder::create(function (AIChatRequest $request): AIChatResponse {
            $response = $this->handle($request);
            Assert::isInstanceOf($response, AIChatResponse::class);

            return $response;
        })->addMessages($messages);
    }
}
