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

use ModelflowAi\Chat\Adapter\AIChatAdapterInterface;
use ModelflowAi\Chat\AIChatRequestHandler;
use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\DecisionTree\Criteria\PrivacyCriteria;
use ModelflowAi\DecisionTree\DecisionRule;
use ModelflowAi\DecisionTree\DecisionTree;
use ModelflowAi\DecisionTree\DecisionTreeInterface;
use ModelflowAi\OllamaAdapter\Chat\OllamaChatAdapter;

$client = require_once \dirname(__DIR__) . '/bootstrap.php';

$adapter = [];

$llama2Adapter = new OllamaChatAdapter($client, 'llama3.2');

/** @var DecisionRule<AIChatRequest, AIChatAdapterInterface> $rule */
$rule = new DecisionRule($llama2Adapter, [PrivacyCriteria::HIGH]);
$adapter[] = $rule;

/** @var DecisionTreeInterface<AIChatRequest, AIChatAdapterInterface> $decisionTree */
$decisionTree = new DecisionTree($adapter);

return new AIChatRequestHandler($decisionTree);
