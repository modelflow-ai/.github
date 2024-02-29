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

namespace ModelflowAi\Ollama\Tests;

final class DataFixtures
{
    private function __construct()
    {
    }

    public const CHAT_CREATE_REQUEST = [
        'model' => 'llama2',
        'messages' => [
            ['role' => 'system', 'content' => 'System message'],
            ['role' => 'user', 'content' => 'User message'],
            ['role' => 'assistant', 'content' => 'Assistant message'],
        ],
    ];

    public const CHAT_CREATE_WITH_IMAGE_REQUEST = [
        'model' => 'llama2',
        'messages' => [
            ['role' => 'system', 'content' => 'System message'],
            ['role' => 'user', 'content' => 'User message'],
            ['role' => 'assistant', 'content' => 'Assistant message', 'images' => [
                'iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg==',
            ]],
        ],
    ];

    public const CHAT_CREATE_RESPONSE = [
        'model' => 'llama2',
        'created_at' => '2024-01-13T12:01:31.929209Z',
        'message' => [
            'role' => 'assistant',
            'content' => 'Lorem Ipsum',
        ],
        'done' => true,
        'total_duration' => 6_259_208_916,
        'load_duration' => 3_882_375,
        'prompt_eval_duration' => 267_650_000,
        'prompt_eval_count' => 0,
        'eval_count' => 169,
        'eval_duration' => 5_981_849_000,
    ];

    public const CHAT_CREATE_STREAMED_RESPONSES = [
        [
            'model' => 'llama2',
            'created_at' => '2024-01-13T12:01:31.929209Z',
            'message' => [
                'role' => 'assistant',
                'content' => 'Lorem',
            ],
            'done' => false,
        ],
        [
            'model' => 'llama2',
            'created_at' => '2024-01-13T12:01:31.929209Z',
            'message' => [
                'role' => 'assistant',
                'content' => 'Ipsum',
            ],
            'done' => true,
        ],
    ];

    public const COMPLETION_CREATE_REQUEST = [
        'model' => 'llama2',
        'prompt' => 'Prompt message',
    ];

    public const COMPLETION_CREATE_RESPONSE = [
        'model' => 'llama2',
        'created_at' => '2024-01-13T12:01:31.929209Z',
        'response' => 'Lorem Ipsum',
        'context' => [0.1, 0.2, 0.3],
        'done' => true,
        'total_duration' => 6_259_208_916,
        'load_duration' => 3_882_375,
        'prompt_eval_duration' => 267_650_000,
        'prompt_eval_count' => 0,
        'eval_count' => 169,
        'eval_duration' => 5_981_849_000,
    ];

    public const COMPLETION_CREATE_STREAMED_RESPONSES = [
        [
            'model' => 'llama2',
            'created_at' => '2024-01-13T12:01:31.929209Z',
            'response' => 'Lorem',
            'context' => [0.1, 0.2, 0.3],
            'done' => false,
        ],
        [
            'model' => 'llama2',
            'created_at' => '2024-01-13T12:01:31.929209Z',
            'response' => 'Ipsum',
            'context' => [0.1, 0.2, 0.3],
            'done' => true,
        ],
    ];

    public const EMBEDDINGS_CREATE_REQUEST = [
        'model' => 'llama2',
        'prompt' => 'Hello WORLD',
    ];

    public const EMBEDDINGS_CREATE_RESPONSE = [
        'embedding' => [0.1, 0.2, 0.3],
    ];
}
