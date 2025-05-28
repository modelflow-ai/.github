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
use ModelflowAi\Chat\ToolInfo\ToolChoiceEnum;
use ModelflowAi\DecisionTree\Criteria\CapabilityCriteria;

require_once __DIR__ . '/WeatherTool.php';

/** @var AIChatRequestHandlerInterface $handler */
$handler = require_once __DIR__ . '/bootstrap.php';

// Create and execute the streaming request - the middleware handles all tool execution automatically
$response = $handler->createStreamedRequest()
    ->addUserMessage('How is the weather in hohenems and vienna?')
    ->tool('get_current_weather', new WeatherTool(), 'getCurrentWeather')
    ->toolChoice(ToolChoiceEnum::AUTO)
    ->addCriteria(CapabilityCriteria::SMART)
    ->execute();

// Output the streamed response
foreach ($response->getMessageStream() as $index => $message) {
    if (0 === $index) {
        echo $message->role->value . ': ';
    }

    echo $message->content;
}

// Output usage
echo "\n\n";
echo 'Usage: ' . ($response->getUsage()?->totalTokens ?? 0) . ' tokens';
