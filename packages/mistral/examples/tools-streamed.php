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

use ModelflowAi\Mistral\Mistral;
use ModelflowAi\Mistral\Model;
use ModelflowAi\Mistral\Responses\Chat\CreateStreamedResponseToolCall;
use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

(new Dotenv())->bootEnv(__DIR__ . '/.env');

$client = Mistral::client($_ENV['MISTRAL_API_KEY']);

/**
 * @return array{
 *     location: string,
 *     timestamp: int,
 *     weather: string,
 *     temperature: float,
 * }
 */
function getCurrentWeather(string $location, ?int $timestamp): array
{
    return [
        'location' => $location,
        'timestamp' => $timestamp ?? \time(),
        'weather' => 'sunny',
        'temperature' => 22.5,
    ];
}

$messages = [
    ['role' => 'user', 'content' => 'What is the weather in Hohenems and Dornbirn?'],
];
$tools = [
    [
        'type' => 'function',
        'function' => [
            'name' => 'get_weather',
            'description' => 'Get the current weather for a single location. Call it multiple times for different locations.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'location' => [
                        'type' => 'string',
                        'description' => 'The location to get the weather for.',
                    ],
                    'timestamp' => [
                        'type' => 'integer',
                        'description' => 'Timestamp to get the weather.',
                    ],
                ],
            ],
            'required' => ['location'],
        ],
    ],
];

$responses = $client->chat()->createStreamed([
    'model' => Model::LARGE->value,
    'messages' => $messages,
    'tools' => $tools,
]);

$additionalMessages = [];
$toolCalls = [];

foreach ($responses as $response) {
    $toolCalls = \array_merge($toolCalls, $response->choices[0]->delta->toolCalls);

    foreach ($response->choices[0]->delta->toolCalls as $toolCall) {
        /** @var array{timestamp?: int|string, location: string} $arguments */
        $arguments = \json_decode($toolCall->function->arguments, true);
        $timestamp = $arguments['timestamp'] ?? null;
        if (\is_string($timestamp)) {
            $timestamp = (int) $timestamp;
        }
        $result = getCurrentWeather($arguments['location'], $timestamp);

        $additionalMessages[] = [
            'role' => 'tool',
            'name' => $toolCall->function->name,
            'content' => (string) \json_encode($result),
        ];
    }
}

$messages[] = [
    'role' => 'assistant',
    'content' => '',
    'tool_calls' => \array_map(
        fn (CreateStreamedResponseToolCall $toolCall): array => [
            'function' => [
                'name' => $toolCall->function->name,
                'arguments' => $toolCall->function->arguments,
            ],
        ],
        $toolCalls,
    ),
];

$messages = \array_merge($messages, $additionalMessages);

$response = $client->chat()->create([
    'model' => Model::LARGE->value,
    'messages' => $messages,
    'tools' => $tools,
]);

echo $response->choices[0]->message->content . \PHP_EOL;
