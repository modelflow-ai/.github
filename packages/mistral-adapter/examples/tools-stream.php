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

use ModelflowAi\Core\AIRequestHandlerInterface;
use ModelflowAi\Core\Request\Builder\AIChatRequestBuilder;
use ModelflowAi\Core\Request\Criteria\CapabilityCriteria;
use ModelflowAi\Core\Request\Message\AIChatMessage;
use ModelflowAi\Core\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Core\Request\Message\ToolCallsPart;
use ModelflowAi\Core\Response\AIChatResponseStream;
use ModelflowAi\Core\ToolInfo\ToolChoiceEnum;
use ModelflowAi\Core\ToolInfo\ToolExecutor;

require_once __DIR__ . '/WeatherTool.php';

/** @var AIRequestHandlerInterface $handler */
$handler = require_once __DIR__ . '/bootstrap.php';

$toolExecutor = new ToolExecutor();

/** @var AIChatRequestBuilder $builder */
$builder = $handler->createChatRequest()
    ->addUserMessage('How is the weather in hohenems and vienna?')
    ->tool('get_current_weather', new WeatherTool(), 'getCurrentWeather')
    ->toolChoice(ToolChoiceEnum::AUTO)
    ->addCriteria(CapabilityCriteria::SMART)
    ->streamed();

$request = $builder->build();

/** @var AIChatResponseStream $response */
$response = $request->execute();

foreach ($response->getMessageStream() as $message) {
    $toolCalls = $message->toolCalls;
    if (null !== $toolCalls && 0 < \count($toolCalls)) {
        $builder->addMessage(
            new AIChatMessage(AIChatMessageRoleEnum::ASSISTANT, ToolCallsPart::create($toolCalls)),
        );

        foreach ($toolCalls as $toolCall) {
            $builder->addMessage(
                $toolExecutor->execute($request, $toolCall),
            );
        }
    }
}

/** @var AIChatResponseStream $response */
$response = $builder->build()->execute();
foreach ($response->getMessageStream() as $index => $message) {
    if (0 === $index) {
        echo $message->role->value . ': ';
    }

    echo $message->content;
}
