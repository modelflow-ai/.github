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
use ModelflowAi\Core\Request\Message\AIChatMessage;
use ModelflowAi\Core\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Core\Request\Message\ToolCallsPart;
use ModelflowAi\Core\ToolInfo\ToolChoiceEnum;

/** @var AIRequestHandlerInterface $handler */
$handler = require_once __DIR__ . '/bootstrap.php';

class WeatherTool
{
    function getCurrentWeather(string $location, ?int $timestamp): array
    {
        return [
            'location' => $location,
            'timestamp' => $timestamp ?? time(),
            'weather' => 'sunny',
            'temperature' => 22.5,
        ];
    }
}

$builder = $handler->createChatRequest()
    ->addUserMessage('How is the weather in hohenems?')
    ->addCriteria(ProviderCriteria::OPENAI)
    ->tool('get_current_weather', new WeatherTool(), 'getCurrentWeather')
    ->toolChoice(ToolChoiceEnum::AUTO);

$request = $builder->build();
$response = $request->execute();

do {
    $toolCalls = $response->getMessage()->toolCalls;
    if ($toolCalls !== null && 0 < \count($toolCalls)) {
        $builder->addMessage(
            new AIChatMessage(AIChatMessageRoleEnum::ASSISTANT, ToolCallsPart::create($toolCalls)),
        );

        foreach ($toolCalls as $toolCall) {
            $builder->addMessage(
                $handler->handleTool($request, $toolCall),
            );
        }

        $response = $builder->build()->execute();
    }
} while ($toolCalls !== null && 0 < \count($toolCalls));

echo $response->getMessage()->content;
