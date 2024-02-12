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
use ModelflowAi\Core\Request\Criteria\CapabilityCriteria;
use ModelflowAi\Core\Request\Criteria\PrivacyCriteria;
use ModelflowAi\Mistral\Mistral;
use ModelflowAi\Mistral\Model;
use ModelflowAi\MistralAdapter\Model\MistralChatModelAdapter;
use ModelflowAi\Ollama\Ollama;
use ModelflowAi\OllamaAdapter\Model\OllamaChatModelAdapter;
use ModelflowAi\OllamaAdapter\Model\OllamaTextModelAdapter;
use ModelflowAi\OpenaiAdapter\Model\OpenaiChatModelAdapter;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(__DIR__ . '/.env');

$adapter = [];

$mistralApiKey = $_ENV['MISTRAL_API_KEY'];
if ($mistralApiKey) {
    $mistralClient = Mistral::client($mistralApiKey);
    $mistralChatAdapter = new MistralChatModelAdapter($mistralClient, Model::MEDIUM);

    $adapter[] = new DecisionRule($mistralChatAdapter, [PrivacyCriteria::MEDIUM]);
}

$openaiApiKey = $_ENV['OPENAI_KEY'];
if ($openaiApiKey) {
    $openAiClient = \OpenAI::client($openaiApiKey);
    $gpt4Adapter = new OpenaiChatModelAdapter($openAiClient);

    $adapter[] = new DecisionRule($gpt4Adapter, [PrivacyCriteria::LOW, CapabilityCriteria::SMART]);
}

$client = Ollama::client();
$llama2ChatAdapter = new OllamaChatModelAdapter($client);
$llama2TextAdapter = new OllamaTextModelAdapter($client);

$adapter[] = new DecisionRule($llama2TextAdapter, [PrivacyCriteria::HIGH]);
$adapter[] = new DecisionRule($llama2ChatAdapter, [PrivacyCriteria::HIGH]);

$decisionTree = new AIModelDecisionTree($adapter);

return new AIRequestHandler($decisionTree);
