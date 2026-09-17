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

use ModelflowAi\Anthropic\Anthropic;
use ModelflowAi\Anthropic\Model;
use ModelflowAi\AnthropicAdapter\Chat\AnthropicChatAdapter;
use ModelflowAi\Chat\Adapter\AIChatAdapterInterface;
use ModelflowAi\Chat\AIChatRequestHandler;
use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\DecisionTree\Criteria\CapabilityCriteria;
use ModelflowAi\DecisionTree\DecisionRule;
use ModelflowAi\DecisionTree\DecisionTree;
use ModelflowAi\DecisionTree\DecisionTreeInterface;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(__DIR__ . '/.env');

$adapter = [];

$anthropicApiKey = $_ENV['ANTHROPIC_API_KEY'];
if (!$anthropicApiKey) {
    throw new \RuntimeException('Anthropic API key is required');
}

$anthropicClient = Anthropic::client($anthropicApiKey);

$opusAdapter = new AnthropicChatAdapter($anthropicClient, Model::CLAUDE_3_OPUS->value);
$sonnetAdapter = new AnthropicChatAdapter($anthropicClient, Model::CLAUDE_3_SONNET->value);
$haikuAdapter = new AnthropicChatAdapter($anthropicClient, Model::CLAUDE_3_HAIKU->value);

$adapter[] = new DecisionRule($opusAdapter, [CapabilityCriteria::SMART]);
$adapter[] = new DecisionRule($sonnetAdapter, [CapabilityCriteria::INTERMEDIATE]);
$adapter[] = new DecisionRule($haikuAdapter, [CapabilityCriteria::BASIC]);

/** @var DecisionTreeInterface<AIChatRequest, AIChatAdapterInterface> $decisionTree */
$decisionTree = new DecisionTree($adapter);

return new AIChatRequestHandler($decisionTree);
