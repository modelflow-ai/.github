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

require_once \dirname(__DIR__) . '/vendor/autoload.php';

use ModelflowAi\Core\AIRequestHandler;
use ModelflowAi\Core\DecisionTree\AIModelDecisionTree;
use ModelflowAi\Core\DecisionTree\DecisionRule;
use ModelflowAi\Core\Request\Criteria\CapabilityCriteria;
use ModelflowAi\Mistral\Mistral;
use ModelflowAi\Mistral\Model;
use ModelflowAi\MistralAdapter\Model\MistralChatModelAdapter;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(__DIR__ . '/.env');

$adapter = [];

$mistralApiKey = $_ENV['MISTRAL_API_KEY'];
if (!$mistralApiKey) {
    throw new \RuntimeException('Mistral API key is required');
}

$mistralClient = Mistral::client($mistralApiKey);

$largeAdapter = new MistralChatModelAdapter($mistralClient, Model::LARGE);
$mediumAdapter = new MistralChatModelAdapter($mistralClient, Model::MEDIUM);
$smallAdapter = new MistralChatModelAdapter($mistralClient, Model::SMALL);
$tinyAdapter = new MistralChatModelAdapter($mistralClient, Model::TINY);

$adapter[] = new DecisionRule($largeAdapter, [CapabilityCriteria::SMART]);
$adapter[] = new DecisionRule($mediumAdapter, [CapabilityCriteria::ADVANCED]);
$adapter[] = new DecisionRule($smallAdapter, [CapabilityCriteria::INTERMEDIATE]);
$adapter[] = new DecisionRule($tinyAdapter, [CapabilityCriteria::BASIC]);

$decisionTree = new AIModelDecisionTree($adapter);

return new AIRequestHandler($decisionTree);
