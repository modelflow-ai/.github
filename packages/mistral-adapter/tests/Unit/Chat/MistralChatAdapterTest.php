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

namespace ModelflowAi\MistralAdapter\Tests\Unit\Chat;

use ModelflowAi\ApiClient\Responses\MetaInformation;
use ModelflowAi\Chat\Request\AIChatMessageCollection;
use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\Chat\Request\AIChatStreamedRequest;
use ModelflowAi\Chat\Request\Message\AIChatMessage;
use ModelflowAi\Chat\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Chat\Request\Message\ImageBase64Part;
use ModelflowAi\Chat\Request\Message\TextPart;
use ModelflowAi\Chat\Request\Message\ToolCallPart;
use ModelflowAi\Chat\Request\Message\ToolCallsPart;
use ModelflowAi\Chat\Request\ResponseFormat\JsonResponseFormat;
use ModelflowAi\Chat\Request\ResponseFormat\JsonSchemaResponseFormat;
use ModelflowAi\Chat\Response\AIChatResponse;
use ModelflowAi\Chat\Response\AIChatResponseStream;
use ModelflowAi\Chat\Response\AIChatToolCall;
use ModelflowAi\Chat\ToolInfo\ToolChoiceEnum;
use ModelflowAi\Chat\ToolInfo\ToolInfoBuilder;
use ModelflowAi\Chat\ToolInfo\ToolTypeEnum;
use ModelflowAi\DecisionTree\Criteria\CriteriaCollection;
use ModelflowAi\Mistral\ClientInterface;
use ModelflowAi\Mistral\Model;
use ModelflowAi\Mistral\Resources\ChatInterface;
use ModelflowAi\Mistral\Responses\Chat\CreateResponse;
use ModelflowAi\Mistral\Responses\Chat\CreateStreamedResponse;
use ModelflowAi\MistralAdapter\Chat\MistralChatAdapter;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class MistralChatAdapterTest extends TestCase
{
    use ProphecyTrait;

    public function testSupports(): void
    {
        $client = $this->prophesize(ClientInterface::class);

        $adapter = new MistralChatAdapter($client->reveal(), Model::TINY->value);

        $request = new AIChatRequest(
            new AIChatMessageCollection(
                new AIChatMessage(AIChatMessageRoleEnum::USER, 'some text'),
            ),
            new CriteriaCollection(),
            [],
            [],
            [],
            static fn () => null,
        );

        $this->assertTrue($adapter->supports($request));
    }

    public function testSupportsWithToolsLargeModel(): void
    {
        $client = $this->prophesize(ClientInterface::class);

        $adapter = new MistralChatAdapter($client->reveal(), Model::LARGE->value);

        $request = new AIChatRequest(
            new AIChatMessageCollection(
                new AIChatMessage(AIChatMessageRoleEnum::USER, 'User message'),
            ),
            new CriteriaCollection(),
            [
                'test' => [$this, 'toolMethod'],
            ],
            [
                ToolInfoBuilder::buildToolInfo($this, 'toolMethod', 'test'),
            ],
            [],
            static fn () => null,
        );

        $this->assertTrue($adapter->supports($request));
    }

    public function testSupportsWithToolsNotSupported(): void
    {
        $client = $this->prophesize(ClientInterface::class);

        $adapter = new MistralChatAdapter($client->reveal(), Model::TINY->value);

        $request = new AIChatRequest(
            new AIChatMessageCollection(
                new AIChatMessage(AIChatMessageRoleEnum::USER, 'User message'),
            ),
            new CriteriaCollection(),
            [
                'test' => [$this, 'toolMethod'],
            ],
            [
                ToolInfoBuilder::buildToolInfo($this, 'toolMethod', 'test'),
            ],
            [],
            static fn () => null,
        );

        $this->assertFalse($adapter->supports($request));
    }

    public function testHandleRequest(): void
    {
        $chat = $this->prophesize(ChatInterface::class);
        $client = $this->prophesize(ClientInterface::class);
        $client->chat()->willReturn($chat->reveal());

        $chat->create([
            'model' => Model::TINY->value,
            'messages' => [
                ['role' => 'user', 'content' => 'some text'],
            ],
        ])->willReturn(
            CreateResponse::from([
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
                ],
                'usage' => [
                    'prompt_tokens' => 312,
                    'completion_tokens' => 324,
                    'total_tokens' => 636,
                ],
            ], MetaInformation::from([])),
        );

        $request = new AIChatRequest(
            new AIChatMessageCollection(
                new AIChatMessage(AIChatMessageRoleEnum::USER, 'some text'),
            ),
            new CriteriaCollection(),
            [],
            [],
            [],
            static fn () => null,
        );

        $adapter = new MistralChatAdapter($client->reveal());
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponse::class, $result);
        $this->assertSame(AIChatMessageRoleEnum::ASSISTANT, $result->getMessage()->role);
        $this->assertSame('Lorem Ipsum', $result->getMessage()->content);
        $this->assertSame(312, $result->getUsage()?->inputTokens);
        $this->assertSame(324, $result->getUsage()->outputTokens);
        $this->assertSame(636, $result->getUsage()->totalTokens);
    }

    public function testHandleRequestWithMultipleParts(): void
    {
        $chat = $this->prophesize(ChatInterface::class);
        $client = $this->prophesize(ClientInterface::class);
        $client->chat()->willReturn($chat->reveal());

        $chat->create([
            'model' => Model::TINY->value,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        ['type' => 'image_url', 'image_url' => 'data:base64;base64,image/png'],
                        ['type' => 'text', 'text' => 'some text'],
                        'result',
                    ],
                    'tool_calls' => [['id' => 'test-id', 'type' => 'function', 'function' => ['name' => 'test', 'arguments' => '{"test":"test"}']]],
                    'tool_call_id' => 'test-id',
                    'name' => 'test',
                ],
            ],
        ])->willReturn(
            CreateResponse::from([
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
                ],
                'usage' => [
                    'prompt_tokens' => 312,
                    'completion_tokens' => 324,
                    'total_tokens' => 636,
                ],
            ], MetaInformation::from([])),
        );

        $request = new AIChatRequest(
            new AIChatMessageCollection(
                new AIChatMessage(AIChatMessageRoleEnum::USER, [
                    new ImageBase64Part('image/png', 'base64'),
                    new TextPart('some text'),
                    new ToolCallsPart([
                        new AIChatToolCall(ToolTypeEnum::FUNCTION, 'test-id', 'test', ['test' => 'test']),
                    ]),
                    new ToolCallPart('test-id', 'test', 'result'),
                ]),
            ),
            new CriteriaCollection(),
            [],
            [],
            [],
            static fn () => null,
        );

        $adapter = new MistralChatAdapter($client->reveal());
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponse::class, $result);
        $this->assertSame(AIChatMessageRoleEnum::ASSISTANT, $result->getMessage()->role);
        $this->assertSame('Lorem Ipsum', $result->getMessage()->content);
        $this->assertSame(312, $result->getUsage()?->inputTokens);
        $this->assertSame(324, $result->getUsage()->outputTokens);
        $this->assertSame(636, $result->getUsage()->totalTokens);
    }

    public function testHandleRequestWithOptions(): void
    {
        $chat = $this->prophesize(ChatInterface::class);
        $client = $this->prophesize(ClientInterface::class);
        $client->chat()->willReturn($chat->reveal());

        $chat->create([
            'model' => Model::TINY->value,
            'messages' => [
                ['role' => 'user', 'content' => 'some text'],
            ],
            'random_seed' => 123,
            'temperature' => 0.5,
        ])->willReturn(
            CreateResponse::from([
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
                ],
                'usage' => [
                    'prompt_tokens' => 312,
                    'completion_tokens' => 324,
                    'total_tokens' => 336,
                ],
            ], MetaInformation::from([])),
        );

        $request = new AIChatRequest(
            new AIChatMessageCollection(
                new AIChatMessage(AIChatMessageRoleEnum::USER, 'some text'),
            ),
            new CriteriaCollection(),
            [],
            [],
            [
                'seed' => 123,
                'temperature' => 0.5,
            ],
            static fn () => null,
        );

        $adapter = new MistralChatAdapter($client->reveal());
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponse::class, $result);
        $this->assertSame(AIChatMessageRoleEnum::ASSISTANT, $result->getMessage()->role);
        $this->assertSame('Lorem Ipsum', $result->getMessage()->content);
    }

    public function testHandleRequestAsJsonIgnoreForNonLargeModel(): void
    {
        $chat = $this->prophesize(ChatInterface::class);
        $client = $this->prophesize(ClientInterface::class);
        $client->chat()->willReturn($chat->reveal());

        $chat->create([
            'model' => Model::TINY->value,
            'messages' => [
                ['role' => 'user', 'content' => 'some text'],
            ],
        ])->willReturn(
            CreateResponse::from([
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
                ],
                'usage' => [
                    'prompt_tokens' => 312,
                    'completion_tokens' => 324,
                    'total_tokens' => 336,
                ],
            ], MetaInformation::from([])),
        );

        $request = new AIChatRequest(
            new AIChatMessageCollection(
                new AIChatMessage(AIChatMessageRoleEnum::USER, 'some text'),
            ),
            new CriteriaCollection(),
            [],
            [],
            [],
            static fn () => null,
            responseFormat: new JsonResponseFormat(),
        );

        $adapter = new MistralChatAdapter($client->reveal());
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponse::class, $result);
        $this->assertSame(AIChatMessageRoleEnum::ASSISTANT, $result->getMessage()->role);
        $this->assertSame('Lorem Ipsum', $result->getMessage()->content);
    }

    public function testHandleRequestAsJsonForLargeModel(): void
    {
        $chat = $this->prophesize(ChatInterface::class);
        $client = $this->prophesize(ClientInterface::class);
        $client->chat()->willReturn($chat->reveal());

        $chat->create([
            'model' => Model::LARGE->value,
            'messages' => [
                ['role' => 'user', 'content' => 'some text'],
            ],
            'response_format' => ['type' => 'json_object'],
        ])->willReturn(
            CreateResponse::from([
                'id' => 'cmpl-e5cc70bb28c444948073e77776eb30ef',
                'object' => 'chat.completion',
                'created' => 1_702_256_327,
                'model' => Model::LARGE->value,
                'choices' => [
                    [
                        'index' => 1,
                        'message' => [
                            'role' => 'assistant',
                            'content' => '{"message": "Lorem Ipsum"}',
                        ],
                        'finish_reason' => 'testFinishReason',
                    ],
                ],
                'usage' => [
                    'prompt_tokens' => 312,
                    'completion_tokens' => 324,
                    'total_tokens' => 636,
                ],
            ], MetaInformation::from([])),
        );

        $request = new AIChatRequest(
            new AIChatMessageCollection(
                new AIChatMessage(AIChatMessageRoleEnum::USER, 'some text'),
            ),
            new CriteriaCollection(),
            [],
            [],
            [],
            static fn () => null,
            [],
            new JsonResponseFormat(),
        );

        $adapter = new MistralChatAdapter($client->reveal(), Model::LARGE->value);
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponse::class, $result);
        $this->assertSame(AIChatMessageRoleEnum::ASSISTANT, $result->getMessage()->role);
        $this->assertSame('{"message": "Lorem Ipsum"}', $result->getMessage()->content);
        $this->assertSame(312, $result->getUsage()?->inputTokens);
        $this->assertSame(324, $result->getUsage()->outputTokens);
        $this->assertSame(636, $result->getUsage()->totalTokens);
    }

    public function testHandleRequestStreamed(): void
    {
        $chat = $this->prophesize(ChatInterface::class);
        $client = $this->prophesize(ClientInterface::class);
        $client->chat()->willReturn($chat->reveal());

        $chat->createStreamed([
            'model' => 'mistral-tiny',
            'messages' => [
                ['role' => 'system', 'content' => 'System message'],
                ['role' => 'user', 'content' => 'User message'],
                ['role' => 'assistant', 'content' => 'Assistant message'],
            ],
        ])->willReturn(
            new \ArrayIterator([
                CreateStreamedResponse::from(0, [
                    'id' => '123-123-123',
                    'model' => 'mistral-tiny',
                    'object' => 'chat.completion',
                    'created' => 1_702_256_327,
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
                    'usage' => null,
                ], MetaInformation::from([])),
                CreateStreamedResponse::from(1, [
                    'id' => '123-123-123',
                    'model' => 'mistral-tiny',
                    'object' => 'chat.completion',
                    'created' => 1_702_256_327,
                    'choices' => [
                        [
                            'index' => 1,
                            'delta' => [
                                'role' => 'assistant',
                                'content' => 'Ipsum',
                            ],
                            'finish_reason' => null,
                        ],
                    ],
                    'usage' => null,
                ], MetaInformation::from([])),
            ], ),
        );

        $request = new AIChatStreamedRequest(
            new AIChatMessageCollection(
                new AIChatMessage(AIChatMessageRoleEnum::SYSTEM, 'System message'),
                new AIChatMessage(AIChatMessageRoleEnum::USER, 'User message'),
                new AIChatMessage(AIChatMessageRoleEnum::ASSISTANT, 'Assistant message'),
            ),
            new CriteriaCollection(),
            [],
            [],
            [],
            static fn () => null,
        );

        $adapter = new MistralChatAdapter($client->reveal());
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponseStream::class, $result);
        $contents = ['Lorem', 'Ipsum'];
        foreach ($result->getMessageStream() as $i => $response) {
            $this->assertSame(AIChatMessageRoleEnum::ASSISTANT, $response->role);
            $this->assertSame($contents[$i], $response->content);
        }
    }

    public function testHandleRequestWithTools(): void
    {
        $chat = $this->prophesize(ChatInterface::class);
        $client = $this->prophesize(ClientInterface::class);
        $client->chat()->willReturn($chat->reveal());

        $chat->create([
            'model' => Model::LARGE->value,
            'messages' => [
                ['role' => 'user', 'content' => 'User message'],
            ],
            'tool_choice' => 'auto',
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
        ])->willReturn(
            CreateResponse::from([
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
            ], MetaInformation::from([])),
        );

        $request = new AIChatRequest(
            new AIChatMessageCollection(
                new AIChatMessage(AIChatMessageRoleEnum::USER, 'User message'),
            ),
            new CriteriaCollection(),
            [
                'test' => [$this, 'toolMethod'],
            ],
            [
                ToolInfoBuilder::buildToolInfo($this, 'toolMethod', 'test'),
            ],
            [],
            static fn () => null,
            toolChoice: ToolChoiceEnum::AUTO,
        );

        $adapter = new MistralChatAdapter($client->reveal(), Model::LARGE->value);
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponse::class, $result);

        $this->assertSame(AIChatMessageRoleEnum::ASSISTANT, $result->getMessage()->role);
        $toolCalls = $result->getMessage()->toolCalls;

        $this->assertNotNull($toolCalls);
        $this->assertCount(2, $toolCalls);

        $toolCall1 = $toolCalls[0];
        $this->assertSame(ToolTypeEnum::FUNCTION, $toolCall1->type);
        $this->assertSame('call_1Ue9UPErEy4dz56T3znEoBO1', $toolCall1->id);
        $this->assertSame('test', $toolCall1->name);
        $this->assertSame([
            'required' => 'Test required 1',
            'optional' => 'Test optional 1',
        ], $toolCall1->arguments);

        $toolCall2 = $toolCalls[1];
        $this->assertSame(ToolTypeEnum::FUNCTION, $toolCall2->type);
        $this->assertSame('call_1Ue9UPErEy4dz56T3znEoBO2', $toolCall2->id);
        $this->assertSame('test', $toolCall2->name);
        $this->assertSame([
            'required' => 'Test required 2',
            'optional' => 'Test optional 2',
        ], $toolCall2->arguments);
    }

    public function testHandleRequestStreamedWithTools(): void
    {
        $chat = $this->prophesize(ChatInterface::class);
        $client = $this->prophesize(ClientInterface::class);
        $client->chat()->willReturn($chat->reveal());

        $chat->createStreamed([
            'model' => Model::LARGE->value,
            'messages' => [
                ['role' => 'user', 'content' => 'User message'],
            ],
            'tool_choice' => 'auto',
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
        ])->willReturn(
            new \ArrayIterator([
                CreateStreamedResponse::from(0, [
                    'id' => '123-123-123',
                    'model' => Model::LARGE->value,
                    'object' => 'chat.completion',
                    'created' => 1_702_256_327,
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
                    'usage' => null,
                ], MetaInformation::from([])),
                CreateStreamedResponse::from(1, [
                    'id' => '123-123-123',
                    'model' => Model::LARGE->value,
                    'object' => 'chat.completion',
                    'created' => 1_702_256_327,
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
                            'finish_reason' => null,
                        ],
                    ],
                    'usage' => null,
                ], MetaInformation::from([])),
            ], ),
        );

        $request = new AIChatStreamedRequest(
            new AIChatMessageCollection(
                new AIChatMessage(AIChatMessageRoleEnum::USER, 'User message'),
            ),
            new CriteriaCollection(),
            [
                'test' => [$this, 'toolMethod'],
            ],
            [
                ToolInfoBuilder::buildToolInfo($this, 'toolMethod', 'test'),
            ],
            [],
            static fn () => null,
        );

        $adapter = new MistralChatAdapter($client->reveal(), Model::LARGE->value);
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponseStream::class, $result);
        $contents = [
            [
                'id' => 'call_1Ue9UPErEy4dz56T3znEoBO1',
                'name' => 'test',
                'arguments' => [
                    'required' => 'Test required 1',
                    'optional' => 'Test optional 1',
                ],
            ],
            [
                'id' => 'call_1Ue9UPErEy4dz56T3znEoBO2',
                'name' => 'test',
                'arguments' => [
                    'required' => 'Test required 2',
                    'optional' => 'Test optional 2',
                ],
            ],
        ];
        foreach ($result->getMessageStream() as $i => $response) {
            $this->assertSame(AIChatMessageRoleEnum::ASSISTANT, $result->getMessage()->role);
            $this->assertNotNull($response->toolCalls);
            $this->assertCount(1, $response->toolCalls);

            $toolCall = $response->toolCalls[0] ?? null;
            $this->assertNotNull($toolCall);
            $this->assertSame(ToolTypeEnum::FUNCTION, $toolCall->type);
            $this->assertSame($contents[$i]['id'], $toolCall->id);
            $this->assertSame($contents[$i]['name'], $toolCall->name);
            $this->assertSame($contents[$i]['arguments'], $toolCall->arguments);
        }
    }

    public function testSupportsResponseFormatJsonWhenModelSupportsJson(): void
    {
        $client = $this->prophesize(ClientInterface::class);

        $adapter = new MistralChatAdapter($client->reveal(), Model::LARGE->value);

        $this->assertTrue($adapter->supportsResponseFormat(new JsonResponseFormat()));
    }

    public function testSupportsResponseFormatJsonWhenModelDoesNotSupportJson(): void
    {
        $client = $this->prophesize(ClientInterface::class);

        $adapter = new MistralChatAdapter($client->reveal(), Model::TINY->value);

        $this->assertFalse($adapter->supportsResponseFormat(new JsonResponseFormat()));
    }

    public function testSupportsResponseFormatJsonSchemaWhenModelSupportsJson(): void
    {
        $client = $this->prophesize(ClientInterface::class);

        $adapter = new MistralChatAdapter($client->reveal(), Model::LARGE->value);

        $this->assertTrue($adapter->supportsResponseFormat(new JsonSchemaResponseFormat([
            'type' => 'object',
            'properties' => [],
        ])));
    }

    public function testSupportsResponseFormatJsonWhenSmallModelSupportsJson(): void
    {
        $client = $this->prophesize(ClientInterface::class);

        $adapter = new MistralChatAdapter($client->reveal(), Model::SMALL->value);

        $this->assertTrue($adapter->supportsResponseFormat(new JsonResponseFormat()));
    }

    public function testSupportsResponseFormatJsonSchemaWhenSmallModelSupportsJson(): void
    {
        $client = $this->prophesize(ClientInterface::class);

        $adapter = new MistralChatAdapter($client->reveal(), Model::SMALL->value);

        $this->assertTrue($adapter->supportsResponseFormat(new JsonSchemaResponseFormat([
            'type' => 'object',
            'properties' => [],
        ])));
    }

    /**
     * This is a description.
     *
     * @param string $required this is a required parameter
     * @param string $optional this is an optional parameter
     */
    public function toolMethod(string $required, string $optional = ''): string
    {
        return $required . $optional;
    }
}
