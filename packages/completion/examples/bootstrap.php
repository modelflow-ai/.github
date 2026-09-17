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

require_once __DIR__ . '/../vendor/autoload.php';

use ModelflowAi\Completion\Adapter\AICompletionAdapterInterface;
use ModelflowAi\Completion\Adapter\Fake\FakeCompletionAdapter;
use ModelflowAi\Completion\AICompletionRequestHandler;
use ModelflowAi\Completion\Request\AICompletionRequest;
use ModelflowAi\DecisionTree\Criteria\PrivacyCriteria;
use ModelflowAi\DecisionTree\DecisionRule;
use ModelflowAi\DecisionTree\DecisionTree;
use ModelflowAi\DecisionTree\DecisionTreeInterface;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(__DIR__ . '/.env');

$adapter = [];

$fakeAdapter = new FakeCompletionAdapter();

/** @var DecisionRule<AICompletionRequest, AICompletionAdapterInterface> $rule */
$rule = new DecisionRule($fakeAdapter, [PrivacyCriteria::HIGH]);
$adapter[] = $rule;

/** @var DecisionTreeInterface<AICompletionRequest, AICompletionAdapterInterface> $decisionTree */
$decisionTree = new DecisionTree($adapter);

return [
    $fakeAdapter,
    new AICompletionRequestHandler($decisionTree),
];
