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

namespace App;

require_once __DIR__ . '/vendor/autoload.php';

use ModelflowAi\Core\AIRequestHandler;
use ModelflowAi\Core\DecisionTree\AIModelDecisionTree;
use ModelflowAi\Core\DecisionTree\DecisionRule;
use ModelflowAi\Core\Request\Criteria\PerformanceRequirement;
use ModelflowAi\Core\Request\Criteria\PrivacyRequirement;
use ModelflowAi\OllamaAdapter\Model\OllamaModelChatAdapter;
use ModelflowAi\OllamaAdapter\Model\OllamaModelTextAdapter;
use ModelflowAi\OpenaiAdapter\Model\GPT4ModelChatAdapter;
use Symfony\Component\HttpClient\HttpClient;

$httpClient = HttpClient::create();

$llama2ChatAdapter = new OllamaModelChatAdapter($httpClient);
$llama2TextAdapter = new OllamaModelTextAdapter($httpClient);

$openAiKey = \getenv('OPENAI_KEY') ?: '';
$openAiClient = \OpenAI::client($openAiKey);

$gpt4Adapter = new GPT4ModelChatAdapter($openAiClient);

$decisionTree = new AIModelDecisionTree([
    new DecisionRule($llama2TextAdapter, [PrivacyRequirement::HIGH]),
    new DecisionRule($llama2ChatAdapter, [PrivacyRequirement::HIGH]),
    new DecisionRule($gpt4Adapter, [PrivacyRequirement::LOW, PerformanceRequirement::SMART]),
]);

return new AIRequestHandler($decisionTree);
