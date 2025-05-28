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

require_once \dirname(__DIR__, 2) . '/vendor/autoload.php';

use ModelflowAi\Chat\Adapter\AIChatAdapterInterface;
use ModelflowAi\Chat\AIChatRequestHandler;
use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\DecisionTree\Criteria\CapabilityCriteria;
use ModelflowAi\DecisionTree\DecisionRule;
use ModelflowAi\DecisionTree\DecisionTree;
use ModelflowAi\DecisionTree\DecisionTreeInterface;
use ModelflowAi\Mistral\Mistral;
use ModelflowAi\Mistral\Model;
use ModelflowAi\MistralAdapter\Chat\MistralChatAdapter;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(\dirname(__DIR__) . '/.env');

$adapter = [];

$mistralApiKey = $_ENV['MISTRAL_API_KEY'];
if (!$mistralApiKey) {
    throw new \RuntimeException('Mistral API key is required');
}

$mistralClient = Mistral::client($mistralApiKey);

$largeAdapter = new MistralChatAdapter($mistralClient, Model::LARGE->value);
$mediumAdapter = new MistralChatAdapter($mistralClient, Model::MEDIUM->value);
$smallAdapter = new MistralChatAdapter($mistralClient, Model::SMALL->value);
$tinyAdapter = new MistralChatAdapter($mistralClient, Model::TINY->value);

$adapter[] = new DecisionRule($largeAdapter, [CapabilityCriteria::SMART]);
$adapter[] = new DecisionRule($mediumAdapter, [CapabilityCriteria::ADVANCED]);
$adapter[] = new DecisionRule($smallAdapter, [CapabilityCriteria::INTERMEDIATE]);
$adapter[] = new DecisionRule($tinyAdapter, [CapabilityCriteria::BASIC]);

/** @var DecisionTreeInterface<AIChatRequest, AIChatAdapterInterface> $decisionTree */
$decisionTree = new DecisionTree($adapter);

return new AIChatRequestHandler($decisionTree);
