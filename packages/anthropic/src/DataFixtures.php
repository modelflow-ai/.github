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

namespace ModelflowAi\Anthropic;

final class DataFixtures
{
    final public const MESSAGES_CREATE_REQUEST_RAW = [
        'model' => Model::CLAUDE_3_HAIKU->value,
        'messages' => [
            ['role' => 'system', 'content' => 'You are an angry bot!'],
            ['role' => 'user', 'content' => 'Hello world!'],
        ],
        'max_tokens' => 100,
    ];

    final public const MESSAGES_CREATE_REQUEST = [
        'model' => Model::CLAUDE_3_HAIKU->value,
        'messages' => [
            ['role' => 'user', 'content' => 'Hello world!'],
        ],
        'max_tokens' => 100,
        'system' => 'You are an angry bot!',
    ];

    final public const MESSAGES_CREATE_WITH_TOOLS_REQUEST_RAW = [
        'model' => Model::CLAUDE_3_HAIKU->value,
        'messages' => [
            ['role' => 'system', 'content' => 'You are an angry bot!'],
            ['role' => 'user', 'content' => 'Hello world!'],
        ],
        'tools' => [
            [
                'name' => 'get_weather',
                'description' => 'Get the current weather in a given location.',
                'input_schema' => [
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
            ],
        ],
        'max_tokens' => 100,
    ];

    final public const MESSAGES_CREATE_WITH_TOOLS_REQUEST = [
        'model' => Model::CLAUDE_3_HAIKU->value,
        'messages' => [
            ['role' => 'user', 'content' => 'Hello world!'],
        ],
        'tools' => [
            [
                'name' => 'get_weather',
                'description' => 'Get the current weather in a given location.',
                'input_schema' => [
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
            ],
        ],
        'max_tokens' => 100,
        'system' => 'You are an angry bot!',
    ];

    final public const MESSAGES_CREATE_STREAMED_REQUEST = [
        'model' => Model::CLAUDE_3_HAIKU->value,
        'stream' => true,
        'messages' => [
            ['role' => 'user', 'content' => 'Hello world!'],
        ],
        'max_tokens' => 100,
        'system' => 'You are an angry bot!',
    ];

    final public const MESSAGES_CREATE_RESPONSE = [
        'id' => 'msg_01BuhEtxBn5xsnZ8diqRLNNM',
        'type' => 'message',
        'role' => 'assistant',
        'model' => Model::CLAUDE_3_HAIKU->value,
        'stop_sequence' => null,
        'usage' => [
            'input_tokens' => 16,
            'output_tokens' => 34,
        ],
        'content' => [
            0 => [
                'type' => 'text',
                'text' => 'Hello! I\'m an AI assistant created by Anthropic. I\'m here to help with all sorts of tasks. How can I assist you today?',
            ],
        ],
        'stop_reason' => 'end_turn',
    ];

    final public const MESSAGES_CREATE_WITH_TOOLS_RESPONSE = [
        'id' => 'msg_01BuhEtxBn5xsnZ8diqRLNNM',
        'type' => 'message',
        'role' => 'assistant',
        'model' => Model::CLAUDE_3_HAIKU->value,
        'stop_sequence' => null,
        'usage' => [
            'input_tokens' => 16,
            'output_tokens' => 34,
        ],
        'content' => [
            0 => [
                'type' => 'tool_use',
                'id' => 'toolu_01W7iPphiNtxfbEfsisKFGtd',
                'name' => 'get_weather',
                'input' => [
                    'location' => 'New York',
                    'timestamp' => 1_681_926_000,
                ],
            ],
        ],
        'stop_reason' => 'end_turn',
    ];

    public const MESSAGES_CREATE_STREAMED_RESPONSES_RAW = [
        [
            'event: message_start',
            'data: {"type": "message_start", "message": {"id": "msg_1nZdL29xx5MUA1yADyHTEsnR8uuvGzszyY", "type": "message", "role": "assistant", "content": [], "model": "claude-3-opus-20240229", "stop_reason": null, "stop_sequence": null, "usage": {"input_tokens": 25, "output_tokens": 1}}}',
        ],
        [
            'event: content_block_start',
            'data: {"type": "content_block_start", "index": 0, "content_block": {"type": "text", "text": ""}}',
        ],
        [
            'event: ping',
            'data: {"type": "ping"}',
        ],
        [
            'event: content_block_delta',
            'data: {"type": "content_block_delta", "index": 0, "delta": {"type": "text_delta", "text": "Hello"}}',
        ],
        [
            'event: content_block_delta',
            'data: {"type": "content_block_delta", "index": 0, "delta": {"type": "text_delta", "text": "!"}}',
        ],
        [
            'event: content_block_stop',
            'data: {"type": "content_block_stop", "index": 0}',
        ],
        [
            'event: message_delta',
            'data: {"type": "message_delta", "delta": {"stop_reason": "end_turn", "stop_sequence":null}, "usage": {"output_tokens": 15}}',
        ],
        [
            'event: message_stop',
            'data: {"type": "message_stop"}',
        ],
    ];

    public const MESSAGES_CREATE_STREAMED_WITH_TOOLS_RESPONSES_RAW = [
        [
            'event: message_start',
            'data: {"type": "message_start", "message": {"id": "msg_tool_stream", "type": "message", "role": "assistant", "content": [], "model": "claude-3-opus-20240229", "stop_reason": null, "stop_sequence": null, "usage": {"input_tokens": 25, "output_tokens": 1}}}',
        ],
        [
            'event: content_block_start',
            'data: {"type": "content_block_start", "index": 0, "content_block": {"type": "tool_use", "id": "toolu_01W7iPphiNtxfbEfsisKFGtd", "name": "get_weather", "input": {}}}',
        ],
        [
            'event: content_block_delta',
            'data: {"type": "content_block_delta", "index": 0, "delta": {"type": "input_json_delta", "partial_json": "{\"location\":"}}',
        ],
        [
            'event: content_block_delta',
            'data: {"type": "content_block_delta", "index": 0, "delta": {"type": "input_json_delta", "partial_json": " \"New York\"}"}}',
        ],
        [
            'event: content_block_stop',
            'data: {"type": "content_block_stop", "index": 0}',
        ],
        [
            'event: message_delta',
            'data: {"type": "message_delta", "delta": {"stop_reason": "tool_use", "stop_sequence":null}, "usage": {"output_tokens": 15}}',
        ],
        [
            'event: message_stop',
            'data: {"type": "message_stop"}',
        ],
    ];

    public const MESSAGES_CREATE_STREAMED_RESPONSES = [
        [
            'id' => 'msg_1nZdL29xx5MUA1yADyHTEsnR8uuvGzszyY',
            'type' => 'message',
            'role' => 'assistant',
            'model' => 'claude-3-opus-20240229',
            'stop_sequence' => null,
            'usage' => [
                'input_tokens' => 25,
                'output_tokens' => 1,
            ],
            'content' => null,
            'stop_reason' => null,
        ],
        [
            'id' => 'msg_1nZdL29xx5MUA1yADyHTEsnR8uuvGzszyY',
            'type' => 'message',
            'role' => 'assistant',
            'model' => 'claude-3-opus-20240229',
            'stop_sequence' => null,
            'usage' => [
                'input_tokens' => 25,
                'output_tokens' => 1,
            ],
            'content' => [
                'index' => 0,
                'type' => 'text',
                'text' => '',
            ],
            'stop_reason' => null,
        ],
        [
            'id' => 'msg_1nZdL29xx5MUA1yADyHTEsnR8uuvGzszyY',
            'type' => 'message',
            'role' => 'assistant',
            'model' => 'claude-3-opus-20240229',
            'stop_sequence' => null,
            'usage' => [
                'input_tokens' => 25,
                'output_tokens' => 1,
            ],
            'content' => [
                'index' => 0,
                'type' => 'text_delta',
                'text' => 'Hello',
            ],
            'stop_reason' => null,
        ],
        [
            'id' => 'msg_1nZdL29xx5MUA1yADyHTEsnR8uuvGzszyY',
            'type' => 'message',
            'role' => 'assistant',
            'model' => 'claude-3-opus-20240229',
            'stop_sequence' => null,
            'usage' => [
                'input_tokens' => 25,
                'output_tokens' => 1,
            ],
            'content' => [
                'index' => 0,
                'type' => 'text_delta',
                'text' => '!',
            ],
            'stop_reason' => null,
        ],
        [
            'id' => 'msg_1nZdL29xx5MUA1yADyHTEsnR8uuvGzszyY',
            'type' => 'message',
            'role' => 'assistant',
            'model' => 'claude-3-opus-20240229',
            'stop_sequence' => null,
            'usage' => [
                'input_tokens' => 25,
                'output_tokens' => 16,
            ],
            'content' => null,
            'stop_reason' => 'end_turn',
        ],
    ];

    private function __construct()
    {
    }
}
