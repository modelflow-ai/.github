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
use ModelflowAi\Core\Tool\ToolChoiceEnum;

/** @var AIRequestHandlerInterface $handler */
$handler = require_once __DIR__ . '/bootstrap.php';

function getCurrentWeather(string $location, ?int $timestamp): array
{
    return [
        'location' => $location,
        'timestamp' => $timestamp ?? time(),
        'weather' => 'sunny',
        'temperature' => 22.5,
    ];
}

$response = $handler->createChatRequest()
    ->addUserMessage('How is the weather in hohenems?')
    ->addCriteria(ProviderCriteria::OPENAI)
    ->tool('get_current_weather', fn (string $location, ?int $timestamp) => getCurrentWeather($location, $timestamp))
    ->toolChoice(ToolChoiceEnum::AUTO)
    ->build()
    ->execute();

echo $response->getMessage()->content;
