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

use ModelflowAi\Chat\Adapter\AIChatAdapterInterface;
use ModelflowAi\Chat\Adapter\Fake\FakeChatAdapter;
use ModelflowAi\Chat\AIChatRequestHandler;
use ModelflowAi\Chat\Middleware\Tools\ToolExecutionMiddleware;
use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\DecisionTree\DecisionRule;
use ModelflowAi\DecisionTree\DecisionTree;
use ModelflowAi\DecisionTree\DecisionTreeInterface;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(__DIR__ . '/.env');

$adapter = [];

$fakeAdapter = new FakeChatAdapter();

/** @var DecisionRule<AIChatRequest, AIChatAdapterInterface> $rule */
$rule = new DecisionRule($fakeAdapter);
$adapter[] = $rule;

/** @var DecisionTreeInterface<AIChatRequest, AIChatAdapterInterface> $decisionTree */
$decisionTree = new DecisionTree($adapter);

return [
    $fakeAdapter,
    new AIChatRequestHandler($decisionTree, [
        new ToolExecutionMiddleware(),
    ]),
];
