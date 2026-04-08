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

require_once \dirname(__DIR__) . '/vendor/autoload.php';

use ModelflowAi\Chat\Adapter\AIChatAdapterInterface;
use ModelflowAi\Chat\AIChatRequestHandler;
use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\DecisionTree\Criteria\CapabilityCriteria;
use ModelflowAi\DecisionTree\DecisionRule;
use ModelflowAi\DecisionTree\DecisionTree;
use ModelflowAi\DecisionTree\DecisionTreeInterface;
use ModelflowAi\OpenaiAdapter\Chat\OpenaiChatAdapter;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(__DIR__ . '/.env');

$openaiApiKey = $_ENV['OPENAI_API_KEY'] ?? null;
if (!\is_string($openaiApiKey) || '' === \trim($openaiApiKey)) {
    throw new \RuntimeException('OpenAI API key is required');
}

$openaiClient = OpenAI::client($openaiApiKey);

$openaiAdapter = new OpenaiChatAdapter($openaiClient);

$adapters = [];
$adapters[] = new DecisionRule($openaiAdapter, [CapabilityCriteria::BASIC]);

/** @var DecisionTreeInterface<AIChatRequest, AIChatAdapterInterface> $decisionTree */
$decisionTree = new DecisionTree($adapters);

return new AIChatRequestHandler($decisionTree);
