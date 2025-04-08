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

use ModelflowAi\Chat\Adapter\Fake\FakeChatAdapter;
use ModelflowAi\Chat\AIChatRequestHandlerInterface;
use ModelflowAi\Chat\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Chat\Response\AIChatResponseMessage;
use ModelflowAi\Chat\Response\AIChatToolCall;
use ModelflowAi\Chat\Response\Usage;
use ModelflowAi\Chat\ToolInfo\ToolChoiceEnum;
use ModelflowAi\Chat\ToolInfo\ToolTypeEnum;
use ModelflowAi\DecisionTree\Criteria\PrivacyCriteria;

require_once __DIR__ . '/WeatherTool.php';

/** @var AIChatRequestHandlerInterface $handler */
/** @var FakeChatAdapter $adapter */
[$adapter, $handler] = require_once __DIR__ . '/bootstrap.php';

// Setup fake adapter response for testing
$adapter->addMessage(new AIChatResponseMessage(AIChatMessageRoleEnum::SYSTEM, '', [
    new AIChatToolCall(ToolTypeEnum::FUNCTION, '123-123-123', 'get_current_weather', ['city' => 'hohenems']),
]), new Usage(10, 20, 30));

// Add second tool call after hohenems weather
$adapter->addMessage(new AIChatResponseMessage(AIChatMessageRoleEnum::SYSTEM, '', [
    new AIChatToolCall(ToolTypeEnum::FUNCTION, '456-456-456', 'get_current_weather', ['city' => 'vienna']),
]), new Usage(10, 20, 30));

// Add response for Vienna
$adapter->addMessage([
    new AIChatResponseMessage(AIChatMessageRoleEnum::SYSTEM, 'The '),
    new AIChatResponseMessage(AIChatMessageRoleEnum::SYSTEM, 'weather '),
    new AIChatResponseMessage(AIChatMessageRoleEnum::SYSTEM, 'in '),
    new AIChatResponseMessage(AIChatMessageRoleEnum::SYSTEM, 'Vienna '),
    new AIChatResponseMessage(AIChatMessageRoleEnum::SYSTEM, 'is '),
    new AIChatResponseMessage(AIChatMessageRoleEnum::SYSTEM, 'partly cloudy'),
    new AIChatResponseMessage(AIChatMessageRoleEnum::SYSTEM, ' and '),
    new AIChatResponseMessage(AIChatMessageRoleEnum::SYSTEM, '15 degrees Celsius.'),
], new Usage(20, 40, 60));

// Create and execute the request
$response = $handler->createStreamedRequest()
    ->addUserMessage('How is the weather in hohenems and vienna?')
    ->tool('get_current_weather', new WeatherTool(), 'getCurrentWeather')
    ->toolChoice(ToolChoiceEnum::AUTO)
    ->addCriteria(PrivacyCriteria::HIGH)
    ->execute();

// Stream the response
foreach ($response->getMessageStream() as $index => $message) {
    if (0 === $index) {
        echo $message->role->value . ': ';
    }

    echo $message->content;
}

// Output the usage
echo "\n\n";
echo 'Usage: ' . $response->getUsage()->totalTokens . "\n";
