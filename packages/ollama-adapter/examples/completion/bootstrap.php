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

use ModelflowAi\Completion\Adapter\AICompletionAdapterInterface;
use ModelflowAi\Completion\AICompletionRequestHandler;
use ModelflowAi\Completion\Request\AICompletionRequest;
use ModelflowAi\DecisionTree\Criteria\PrivacyCriteria;
use ModelflowAi\DecisionTree\DecisionRule;
use ModelflowAi\DecisionTree\DecisionTree;
use ModelflowAi\DecisionTree\DecisionTreeInterface;
use ModelflowAi\OllamaAdapter\Completion\OllamaCompletionAdapter;

$ollamaClient = require_once \dirname(__DIR__) . '/bootstrap.php';

$adapter = [];

$ollamaAdapter = new OllamaCompletionAdapter($ollamaClient, 'llama3.2');

/** @var DecisionRule<AICompletionRequest, AICompletionAdapterInterface> $rule */
$rule = new DecisionRule($ollamaAdapter, [PrivacyCriteria::HIGH]);
$adapter[] = $rule;

/** @var DecisionTreeInterface<AICompletionRequest, AICompletionAdapterInterface> $decisionTree */
$decisionTree = new DecisionTree($adapter);

return new AICompletionRequestHandler($decisionTree);
