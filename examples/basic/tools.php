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
use ModelflowAi\Core\Request\Message\AIChatMessage;
use ModelflowAi\Core\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Core\Request\Message\ToolCallsPart;
use ModelflowAi\Core\ToolInfo\ToolChoiceEnum;

require_once __DIR__ . '/WeatherTool.php';

/** @var AIRequestHandlerInterface $handler */
$handler = require_once __DIR__ . '/bootstrap.php';

/** @var AIChatRequestBuilder $builder */
$builder = $handler->createChatRequest()
    ->addUserMessage('How is the weather in hohenems?')
    ->tool('get_current_weather', new WeatherTool(), 'getCurrentWeather')
    ->toolChoice(ToolChoiceEnum::AUTO)
    ->addCriteria(ProviderCriteria::OPENAI);

$request = $builder->build();
$response = $request->execute();

do {
    $toolCalls = $response->getMessage()->toolCalls;
    if (null !== $toolCalls && 0 < \count($toolCalls)) {
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
} while (null !== $toolCalls && [] !== $toolCalls);

echo $response->getMessage()->content;
