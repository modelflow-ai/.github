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
$adapter->addMessage(
    new AIChatResponseMessage(AIChatMessageRoleEnum::SYSTEM, 'The weather in hohenems is sunny'),
    new Usage(10, 20, 30),
);

// Create and execute the request
$response = $handler->createRequest()
    ->addUserMessage('How is the weather in hohenems?')
    ->tool('get_current_weather', new WeatherTool(), 'getCurrentWeather')
    ->toolChoice(ToolChoiceEnum::AUTO)
    ->addCriteria(PrivacyCriteria::HIGH)
    ->execute();

// Output the response
echo $response->getMessage()->role->value . ': ' . $response->getMessage()->content;

// Output the usage
echo "\n\n";
echo 'Usage: ' . $response->getUsage()->totalTokens . "\n";
