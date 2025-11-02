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

use ModelflowAi\Chat\AIChatRequestHandlerInterface;
use ModelflowAi\Chat\Request\Message\AIChatMessage;
use ModelflowAi\Chat\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Chat\Response\Usage;
use ModelflowAi\Chat\Response\UsageCallbackInterface;
use ModelflowAi\DecisionTree\Criteria\CapabilityCriteria;
use ModelflowAi\PromptTemplate\ChatPromptTemplate;

/** @var AIChatRequestHandlerInterface $handler */
$handler = require_once __DIR__ . '/bootstrap.php';

$response = $handler->createStreamedRequest(
    ...ChatPromptTemplate::create(
        new AIChatMessage(AIChatMessageRoleEnum::SYSTEM, 'You are an {feeling} bot'),
        new AIChatMessage(AIChatMessageRoleEnum::USER, 'Hello {where}!'),
    )->format(['where' => 'world', 'feeling' => 'angry']),
)
    ->addCriteria(CapabilityCriteria::SMART)
    ->execute();

// Register a callback to receive usage updates in real-time
$response->registerUsageCallback(new class implements UsageCallbackInterface {
    public function onUsageUpdate(Usage $usage, bool $isFinal): void
    {
        $status = $isFinal ? 'Final' : 'Partial';
        echo \PHP_EOL . "[{$status} Usage: {$usage->totalTokens} tokens]";
    }
});

foreach ($response->getMessageStream() as $index => $message) {
    if (0 === $index) {
        echo $message->role->value . ': ';
    }

    echo $message->content;
}

// Get final usage after stream completes
echo \PHP_EOL . \PHP_EOL;
$usage = $response->getUsage();
if (null !== $usage) {
    echo "Final usage: {$usage->inputTokens} input + {$usage->outputTokens} output = {$usage->totalTokens} total tokens" . \PHP_EOL;
    if ($usage->isEstimated()) {
        echo '(estimated)' . \PHP_EOL;
    }
}
