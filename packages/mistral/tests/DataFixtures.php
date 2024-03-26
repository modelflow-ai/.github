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

    public const CHAT_CREATE_REQUEST_WITH_TOOLS = [
        'model' => Model::LARGE->value,
        'messages' => [
            ['role' => 'system', 'content' => 'System message'],
            ['role' => 'user', 'content' => 'User message'],
            ['role' => 'assistant', 'content' => 'Assistant message'],
        ],
        'tools' => [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'test',
                    'description' => 'This is a description.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'required' => [
                                'type' => 'string',
                                'description' => 'this is a required parameter',
                            ],
                            'optional' => [
                                'type' => 'string',
                                'description' => 'this is an optional parameter',
                            ],
                        ],
                        'required' => ['required'],
                    ],
                ],
            ],
        ],
        'tool_choice' => 'auto',
        'temperature' => 0.8,
        'top_p' => 0.9,
        'max_tokens' => 100,
        'safe_mode' => true,
        'random_seed' => 42,
    ];

    public const CHAT_CREATE_RESPONSE_WITH_TOOLS = [
        'id' => 'cmpl-e5cc70bb28c444948073e77776eb30ef',
        'object' => 'chat.completion',
        'created' => 1_702_256_327,
        'model' => Model::LARGE->value,
        'choices' => [
            [
                'index' => 1,
                'message' => [
                    'role' => 'assistant',
                    'content' => null,
                    'tool_calls' => [
                        [
                            'id' => 'call_1Ue9UPErEy4dz56T3znEoBO1',
                            'type' => 'function',
                            'function' => [
                                'name' => 'test',
                                'arguments' => '{"required":"Test required 1","optional":"Test optional 1"}',
                            ],
                        ],
                        [
                            'id' => 'call_1Ue9UPErEy4dz56T3znEoBO2',
                            'type' => 'function',
                            'function' => [
                                'name' => 'test',
                                'arguments' => '{"required":"Test required 2","optional":"Test optional 2"}',
                            ],
                        ],
                    ],
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

    public const CHAT_CREATE_STREAMED_RESPONSES = [
        [
            'id' => 'cmpl-e5cc70bb28c444948073e77776eb30ef',
            'object' => 'chat.completion',
            'created' => 1_702_256_327,
            'model' => Model::TINY->value,
            'choices' => [
                [
                    'index' => 1,
                    'delta' => [
                        'role' => 'assistant',
                        'content' => 'Lorem',
                    ],
                    'finish_reason' => null,
                ],
            ],
            'usage' => [
                'prompt_tokens' => 312,
                'completion_tokens' => 324,
                'total_tokens' => 336,
            ],
        ],
        [
            'id' => 'cmpl-e5cc70bb28c444948073e77776eb30ef',
            'object' => 'chat.completion',
            'created' => 1_702_256_327,
            'model' => Model::TINY->value,
            'choices' => [
                [
                    'index' => 1,
                    'delta' => [
                        'role' => 'assistant',
                        'content' => 'Ipsum',
                    ],
                    'finish_reason' => 'testFinishReason',
                ],
            ],
        ],
    ];

    public const CHAT_CREATE_STREAMED_RESPONSES_WITH_TOOLS = [
        [
            'id' => 'cmpl-e5cc70bb28c444948073e77776eb30ef',
            'object' => 'chat.completion',
            'created' => 1_702_256_327,
            'model' => Model::TINY->value,
            'choices' => [
                [
                    'index' => 1,
                    'delta' => [
                        'role' => 'assistant',
                        'content' => null,
                        'tool_calls' => [
                            [
                                'id' => 'call_1Ue9UPErEy4dz56T3znEoBO1',
                                'type' => 'function',
                                'function' => [
                                    'name' => 'test',
                                    'arguments' => '{"required":"Test required 1","optional":"Test optional 1"}',
                                ],
                            ],
                        ],
                    ],
                    'finish_reason' => null,
                ],
            ],
            'usage' => [
                'prompt_tokens' => 312,
                'completion_tokens' => 324,
                'total_tokens' => 336,
            ],
        ],
        [
            'id' => 'cmpl-e5cc70bb28c444948073e77776eb30ef',
            'object' => 'chat.completion',
            'created' => 1_702_256_327,
            'model' => Model::TINY->value,
            'choices' => [
                [
                    'index' => 1,
                    'delta' => [
                        'role' => 'assistant',
                        'content' => null,
                        'tool_calls' => [
                            [
                                'id' => 'call_1Ue9UPErEy4dz56T3znEoBO2',
                                'type' => 'function',
                                'function' => [
                                    'name' => 'test',
                                    'arguments' => '{"required":"Test required 2","optional":"Test optional 2"}',
                                ],
                            ],
                        ],
                    ],
                    'finish_reason' => 'testFinishReason',
                ],
            ],
        ],
    ];

    public const EMBEDDINGS_CREATE_REQUEST = [
        'model' => Model::EMBED->value,
        'input' => [
            'Hello',
            'WORLD',
        ],
    ];

    public const EMBEDDINGS_CREATE_RESPONSE = [
        'id' => 'embd-aad6fc62b17349b192ef09225058bc45',
        'object' => 'list',
        'data' => [
            [
                'object' => 'embedding',
                'embedding' => [0.1, 0.2, 0.3],
                'index' => 0,
            ],
            [
                'object' => 'embedding',
                'embedding' => [0.4, 0.5, 0.6],
                'index' => 1,
            ],
        ],
        'model' => Model::EMBED->value,
        'usage' => [
            'prompt_tokens' => 9,
            'total_tokens' => 9,
        ],
    ];
}
