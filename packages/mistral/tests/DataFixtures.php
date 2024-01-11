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

namespace ModelflowAi\Mistral\Tests;

use ModelflowAi\Mistral\Model;

final class DataFixtures
{
    private function __construct()
    {
    }

    public const CHAT_CREATE_REQUEST = [
        'model' => Model::TINY->value,
        'messages' => [
            ['role' => 'system', 'content' => 'System message'],
            ['role' => 'user', 'content' => 'User message'],
            ['role' => 'assistant', 'content' => 'Assistant message'],
        ],
        'temperature' => 0.8,
        'top_p' => 0.9,
        'max_tokens' => 100,
        'safe_mode' => true,
        'random_seed' => 42,
    ];

    public const CHAT_CREATE_RESPONSE = [
        'id' => 'cmpl-e5cc70bb28c444948073e77776eb30ef',
        'object' => 'chat.completion',
        'created' => 1_702_256_327,
        'model' => Model::TINY->value,
        'choices' => [
            [
                'index' => 1,
                'message' => [
                    'role' => 'assistant',
                    'content' => 'Lorem Ipsum',
                ],
                'finish_reason' => 'testFinishReason',
            ],
            [
                'index' => 2,
                'message' => [
                    'role' => 'assistant',
                    'content' => 'Dolor sit amet',
                ],
                'finish_reason' => 'testFinishReason',
            ],
        ],
        'usage' => [
            'prompt_tokens' => 312,
            'completion_tokens' => 324,
            'total_tokens' => 336,
        ],
    ];
}
