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

namespace ModelflowAi\AnthropicAdapter\Tests\Unit\Chat;

use ModelflowAi\Anthropic\Client;
use ModelflowAi\Anthropic\ClientInterface;
use ModelflowAi\Anthropic\DataFixtures;
use ModelflowAi\Anthropic\Model;
use ModelflowAi\AnthropicAdapter\Chat\AnthropicChatAdapter;
use ModelflowAi\ApiClient\Responses\MetaInformation;
use ModelflowAi\ApiClient\Transport\Response\ObjectResponse;
use ModelflowAi\ApiClient\Transport\Testing\MockResponseMatcher;
use ModelflowAi\ApiClient\Transport\Testing\MockTransport;
use ModelflowAi\ApiClient\Transport\Testing\PartialPayload;
use ModelflowAi\ApiClient\Transport\Testing\StreamedResponse;
use ModelflowAi\Chat\Request\AIChatMessageCollection;
use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\Chat\Request\AIChatStreamedRequest;
use ModelflowAi\Chat\Request\Message\AIChatMessage;
use ModelflowAi\Chat\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Chat\Request\Message\ToolCallPart;
use ModelflowAi\Chat\Request\Message\ToolCallsPart;
use ModelflowAi\Chat\Request\ResponseFormat\JsonResponseFormat;
use ModelflowAi\Chat\Request\ResponseFormat\JsonSchemaResponseFormat;
use ModelflowAi\Chat\Request\ResponseFormat\ResponseFormatInterface;
use ModelflowAi\Chat\Response\AIChatResponse;
use ModelflowAi\Chat\Response\AIChatResponseStream;
use ModelflowAi\Chat\Response\AIChatToolCall;
use ModelflowAi\Chat\ToolInfo\ToolInfoBuilder;
use ModelflowAi\Chat\ToolInfo\ToolTypeEnum;
use ModelflowAi\DecisionTree\Criteria\CriteriaCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class AnthropicChatAdapterTest extends TestCase
{
    use ProphecyTrait;

    public function testSupports(): void
    {
        $client = $this->prophesize(ClientInterface::class);

        $adapter = new AnthropicChatAdapter($client->reveal(), Model::CLAUDE_3_SONNET->value);

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

    public function testSupportsWithTools(): void
    {
        $client = $this->prophesize(ClientInterface::class);

        $adapter = new AnthropicChatAdapter($client->reveal(), Model::CLAUDE_3_SONNET->value);

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

    public function testHandleRequest(): void
    {
        $mockResponseMatcher = new MockResponseMatcher();
        $mockResponseMatcher->addResponse(
            PartialPayload::create(
                'messages',
                DataFixtures::MESSAGES_CREATE_REQUEST,
            ),
            new ObjectResponse(DataFixtures::MESSAGES_CREATE_RESPONSE, MetaInformation::empty()),
        );

        $client = new Client(new MockTransport($mockResponseMatcher));

        $request = new AIChatRequest(
            new AIChatMessageCollection(
                new AIChatMessage(
                    AIChatMessageRoleEnum::SYSTEM,
                    DataFixtures::MESSAGES_CREATE_REQUEST_RAW['messages'][0]['content'],
                ),
                new AIChatMessage(
                    AIChatMessageRoleEnum::USER,
                    DataFixtures::MESSAGES_CREATE_REQUEST_RAW['messages'][1]['content'],
                ),
            ),
            new CriteriaCollection(),
            [],
            [],
            [],
            static fn () => null,
        );

        $adapter = new AnthropicChatAdapter($client, Model::CLAUDE_3_HAIKU->value, 100);
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponse::class, $result);
        $this->assertSame(AIChatMessageRoleEnum::ASSISTANT, $result->getMessage()->role);
        $this->assertSame(DataFixtures::MESSAGES_CREATE_RESPONSE['content'][0]['text'], $result->getMessage()->content);
    }

    public function testHandleRequestWithOptions(): void
    {
        $payload = DataFixtures::MESSAGES_CREATE_REQUEST;
        $payload['temperature'] = 0.5;

        $mockResponseMatcher = new MockResponseMatcher();
        $mockResponseMatcher->addResponse(PartialPayload::create(
            'messages',
            $payload,
        ), new ObjectResponse(DataFixtures::MESSAGES_CREATE_RESPONSE, MetaInformation::empty()));

        $client = new Client(new MockTransport($mockResponseMatcher));

        $request = new AIChatRequest(new AIChatMessageCollection(
            new AIChatMessage(AIChatMessageRoleEnum::SYSTEM, DataFixtures::MESSAGES_CREATE_REQUEST_RAW['messages'][0]['content']),
            new AIChatMessage(AIChatMessageRoleEnum::USER, DataFixtures::MESSAGES_CREATE_REQUEST_RAW['messages'][1]['content']),
        ), new CriteriaCollection(), [], [], [
            'seed' => 100,
            'temperature' => 0.5,
        ], static fn () => null);

        $adapter = new AnthropicChatAdapter($client, Model::CLAUDE_3_HAIKU->value, 100);
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponse::class, $result);
        $this->assertSame(AIChatMessageRoleEnum::ASSISTANT, $result->getMessage()->role);
        $this->assertSame(DataFixtures::MESSAGES_CREATE_RESPONSE['content'][0]['text'], $result->getMessage()->content);
        $this->assertSame(
            DataFixtures::MESSAGES_CREATE_RESPONSE['usage']['input_tokens'],
            $result->getUsage()?->inputTokens,
        );
        $this->assertSame(
            DataFixtures::MESSAGES_CREATE_RESPONSE['usage']['output_tokens'],
            $result->getUsage()->outputTokens,
        );
        $this->assertSame(
            DataFixtures::MESSAGES_CREATE_RESPONSE['usage']['input_tokens'] + DataFixtures::MESSAGES_CREATE_RESPONSE['usage']['output_tokens'],
            $result->getUsage()->totalTokens,
        );
    }

    public function testHandleRequestWithJson(): void
    {
        $payload = DataFixtures::MESSAGES_CREATE_REQUEST;
        $payload['messages'][] = ['role' => 'assistant', 'content' => [
            [
                'type' => 'text',
                'text' => '{',
            ],
        ]];

        $mockResponseMatcher = new MockResponseMatcher();
        $mockResponseMatcher->addResponse(PartialPayload::create(
            'messages',
            $payload,
        ), new ObjectResponse(DataFixtures::MESSAGES_CREATE_RESPONSE, MetaInformation::empty()));

        $client = new Client(new MockTransport($mockResponseMatcher));

        $request = new AIChatRequest(new AIChatMessageCollection(
            new AIChatMessage(AIChatMessageRoleEnum::SYSTEM, DataFixtures::MESSAGES_CREATE_REQUEST_RAW['messages'][0]['content']),
            new AIChatMessage(AIChatMessageRoleEnum::USER, DataFixtures::MESSAGES_CREATE_REQUEST_RAW['messages'][1]['content']),
        ), new CriteriaCollection(), [], [], [], static fn () => null, responseFormat: new JsonResponseFormat());

        $adapter = new AnthropicChatAdapter($client, Model::CLAUDE_3_HAIKU->value, 100);
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponse::class, $result);
        $this->assertSame(AIChatMessageRoleEnum::ASSISTANT, $result->getMessage()->role);
        $this->assertSame(DataFixtures::MESSAGES_CREATE_RESPONSE['content'][0]['text'], $result->getMessage()->content);
        $this->assertSame(
            DataFixtures::MESSAGES_CREATE_RESPONSE['usage']['input_tokens'],
            $result->getUsage()?->inputTokens,
        );
        $this->assertSame(
            DataFixtures::MESSAGES_CREATE_RESPONSE['usage']['output_tokens'],
            $result->getUsage()->outputTokens,
        );
        $this->assertSame(
            DataFixtures::MESSAGES_CREATE_RESPONSE['usage']['input_tokens'] + DataFixtures::MESSAGES_CREATE_RESPONSE['usage']['output_tokens'],
            $result->getUsage()->totalTokens,
        );
    }

    public function testHandleRequestWithJsonSchema(): void
    {
        $responseFormat = new JsonSchemaResponseFormat([
            'type' => 'object',
            'properties' => [
                'answer' => ['type' => 'string'],
            ],
            'required' => ['answer'],
        ]);

        $payload = DataFixtures::MESSAGES_CREATE_REQUEST;
        $payload['output_config'] = [
            'format' => [
                'type' => 'json_schema',
                'schema' => $responseFormat->schema,
            ],
        ];

        $mockResponseMatcher = new MockResponseMatcher();
        $mockResponseMatcher->addResponse(PartialPayload::create(
            'messages',
            $payload,
        ), new ObjectResponse(DataFixtures::MESSAGES_CREATE_RESPONSE, MetaInformation::empty()));

        $client = new Client(new MockTransport($mockResponseMatcher));

        $request = new AIChatRequest(new AIChatMessageCollection(
            new AIChatMessage(AIChatMessageRoleEnum::SYSTEM, DataFixtures::MESSAGES_CREATE_REQUEST_RAW['messages'][0]['content']),
            new AIChatMessage(AIChatMessageRoleEnum::USER, DataFixtures::MESSAGES_CREATE_REQUEST_RAW['messages'][1]['content']),
        ), new CriteriaCollection(), [], [], [], static fn () => null, responseFormat: $responseFormat);

        $adapter = new AnthropicChatAdapter($client, Model::CLAUDE_3_HAIKU->value, 100);
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponse::class, $result);
        $this->assertSame(AIChatMessageRoleEnum::ASSISTANT, $result->getMessage()->role);
        $this->assertSame(DataFixtures::MESSAGES_CREATE_RESPONSE['content'][0]['text'], $result->getMessage()->content);
    }

    public function testHandleRequestWithJsonSchemaStripsUnsupportedKeywords(): void
    {
        $responseFormat = new JsonSchemaResponseFormat([
            'type' => 'object',
            'properties' => [
                'items' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'minItems' => 1,
                    'maxItems' => 5,
                    'uniqueItems' => true,
                ],
                'count' => [
                    'type' => 'integer',
                    'minimum' => 0,
                    'maximum' => 10,
                ],
            ],
            'required' => ['items', 'count'],
        ]);

        // Anthropic rejects maxItems/uniqueItems/minimum/maximum; minItems must survive.
        // JsonSchemaResponseFormat also normalizes the schema by adding empty descriptions
        // and additionalProperties: false, which are forwarded to the payload.
        $payload = DataFixtures::MESSAGES_CREATE_REQUEST;
        $payload['output_config'] = [
            'format' => [
                'type' => 'json_schema',
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'items' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                            'minItems' => 1,
                            'description' => '',
                        ],
                        'count' => [
                            'type' => 'integer',
                            'description' => '',
                        ],
                    ],
                    'required' => ['items', 'count'],
                    'description' => '',
                    'additionalProperties' => false,
                ],
            ],
        ];

        $mockResponseMatcher = new MockResponseMatcher();
        $mockResponseMatcher->addResponse(PartialPayload::create(
            'messages',
            $payload,
        ), new ObjectResponse(DataFixtures::MESSAGES_CREATE_RESPONSE, MetaInformation::empty()));

        $client = new Client(new MockTransport($mockResponseMatcher));

        $request = new AIChatRequest(new AIChatMessageCollection(
            new AIChatMessage(AIChatMessageRoleEnum::SYSTEM, DataFixtures::MESSAGES_CREATE_REQUEST_RAW['messages'][0]['content']),
            new AIChatMessage(AIChatMessageRoleEnum::USER, DataFixtures::MESSAGES_CREATE_REQUEST_RAW['messages'][1]['content']),
        ), new CriteriaCollection(), [], [], [], static fn () => null, responseFormat: $responseFormat);

        $adapter = new AnthropicChatAdapter($client, Model::CLAUDE_3_HAIKU->value, 100);
        $result = $adapter->handleRequest($request);

        // Reaching this point proves the sanitized payload matched the mock expectation.
        $this->assertSame(
            DataFixtures::MESSAGES_CREATE_RESPONSE['content'][0]['text'],
            $result->getMessage()->content,
        );
    }

    public function testHandleRequestStreamed(): void
    {
        $mockResponseMatcher = new MockResponseMatcher();
        $client = new Client(new MockTransport($mockResponseMatcher));

        $responseChunks = [];
        foreach (DataFixtures::MESSAGES_CREATE_STREAMED_RESPONSES_RAW as $response) {
            $responseChunks[] = \implode(\PHP_EOL, $response);
        }

        $mockResponseMatcher->addResponse(PartialPayload::create(
            'messages',
            DataFixtures::MESSAGES_CREATE_STREAMED_REQUEST,
        ), new StreamedResponse($responseChunks, MetaInformation::empty()));

        $request = new AIChatStreamedRequest(new AIChatMessageCollection(
            new AIChatMessage(AIChatMessageRoleEnum::SYSTEM, DataFixtures::MESSAGES_CREATE_REQUEST_RAW['messages'][0]['content']),
            new AIChatMessage(AIChatMessageRoleEnum::USER, DataFixtures::MESSAGES_CREATE_REQUEST_RAW['messages'][1]['content']),
        ), new CriteriaCollection(), [], [], [], static fn () => null);

        $adapter = new AnthropicChatAdapter($client, Model::CLAUDE_3_HAIKU->value, 100);
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponseStream::class, $result);
        $contents = ['Hello', '!'];
        foreach ($result->getMessageStream() as $i => $response) {
            $this->assertSame(AIChatMessageRoleEnum::ASSISTANT, $response->role);
            $this->assertSame($contents[$i], $response->content);
        }
    }

    public function testHandleRequestWithTools(): void
    {
        $expectedPayload = [
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
                                'description' => 'the location to get the weather for',
                            ],
                            'timestamp' => [
                                'type' => 'integer',
                                'description' => 'timestamp to get the weather',
                            ],
                        ],
                        'required' => ['location'],
                    ],
                ],
            ],
            'max_tokens' => 100,
            'system' => 'You are an angry bot!',
        ];

        $mockResponseMatcher = new MockResponseMatcher();
        $mockResponseMatcher->addResponse(
            PartialPayload::create('messages', $expectedPayload),
            new ObjectResponse(DataFixtures::MESSAGES_CREATE_WITH_TOOLS_RESPONSE, MetaInformation::empty()),
        );

        $client = new Client(new MockTransport($mockResponseMatcher));

        $request = new AIChatRequest(
            new AIChatMessageCollection(
                new AIChatMessage(
                    AIChatMessageRoleEnum::SYSTEM,
                    DataFixtures::MESSAGES_CREATE_WITH_TOOLS_REQUEST_RAW['messages'][0]['content'],
                ),
                new AIChatMessage(
                    AIChatMessageRoleEnum::USER,
                    DataFixtures::MESSAGES_CREATE_WITH_TOOLS_REQUEST_RAW['messages'][1]['content'],
                ),
            ),
            new CriteriaCollection(),
            [
                'get_weather' => [$this, 'getWeatherMethod'],
            ],
            [
                ToolInfoBuilder::buildToolInfo($this, 'getWeatherMethod', 'get_weather'),
            ],
            [],
            static fn () => null,
        );

        $adapter = new AnthropicChatAdapter($client, Model::CLAUDE_3_HAIKU->value, 100);
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponse::class, $result);
        $this->assertSame(AIChatMessageRoleEnum::ASSISTANT, $result->getMessage()->role);
        $this->assertSame('', $result->getMessage()->content);
        $this->assertNotNull($result->getMessage()->toolCalls);
        $this->assertCount(1, $result->getMessage()->toolCalls);

        $toolCall = $result->getMessage()->toolCalls[0];
        $this->assertSame(ToolTypeEnum::FUNCTION, $toolCall->type);
        $this->assertSame('toolu_01W7iPphiNtxfbEfsisKFGtd', $toolCall->id);
        $this->assertSame('get_weather', $toolCall->name);
        $this->assertSame(['location' => 'New York', 'timestamp' => 1_681_926_000], $toolCall->arguments);
    }

    public function testHandleRequestWithToolCallsPart(): void
    {
        $expectedPayload = [
            'model' => Model::CLAUDE_3_HAIKU->value,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => 'What is the weather in New York?',
                ],
                [
                    'role' => 'assistant',
                    'content' => [
                        [
                            'type' => 'tool_use',
                            'id' => 'toolu_123',
                            'name' => 'get_weather',
                            'input' => ['location' => 'New York'],
                        ],
                    ],
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'tool_result',
                            'tool_use_id' => 'toolu_123',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => 'Sunny, 72°F',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'max_tokens' => 100,
            'system' => '',
        ];

        $mockResponseMatcher = new MockResponseMatcher();
        $mockResponseMatcher->addResponse(
            PartialPayload::create('messages', $expectedPayload),
            new ObjectResponse(DataFixtures::MESSAGES_CREATE_RESPONSE, MetaInformation::empty()),
        );

        $client = new Client(new MockTransport($mockResponseMatcher));

        $toolCall = new AIChatToolCall(
            ToolTypeEnum::FUNCTION,
            'toolu_123',
            'get_weather',
            ['location' => 'New York'],
        );

        $request = new AIChatRequest(
            new AIChatMessageCollection(
                new AIChatMessage(AIChatMessageRoleEnum::USER, 'What is the weather in New York?'),
                new AIChatMessage(AIChatMessageRoleEnum::ASSISTANT, ToolCallsPart::create([$toolCall])),
                new AIChatMessage(AIChatMessageRoleEnum::USER, ToolCallPart::create('toolu_123', 'get_weather', 'Sunny, 72°F')),
            ),
            new CriteriaCollection(),
            [],
            [],
            [],
            static fn () => null,
        );

        $adapter = new AnthropicChatAdapter($client, Model::CLAUDE_3_HAIKU->value, 100);
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponse::class, $result);
        $this->assertSame(AIChatMessageRoleEnum::ASSISTANT, $result->getMessage()->role);
    }

    public function testHandleRequestWithToolCallPart(): void
    {
        $expectedPayload = [
            'model' => Model::CLAUDE_3_HAIKU->value,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'tool_result',
                            'tool_use_id' => 'toolu_456',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => 'Result from tool execution',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'max_tokens' => 100,
            'system' => '',
        ];

        $mockResponseMatcher = new MockResponseMatcher();
        $mockResponseMatcher->addResponse(
            PartialPayload::create('messages', $expectedPayload),
            new ObjectResponse(DataFixtures::MESSAGES_CREATE_RESPONSE, MetaInformation::empty()),
        );

        $client = new Client(new MockTransport($mockResponseMatcher));

        $request = new AIChatRequest(
            new AIChatMessageCollection(
                new AIChatMessage(
                    AIChatMessageRoleEnum::USER,
                    ToolCallPart::create('toolu_456', 'some_tool', 'Result from tool execution'),
                ),
            ),
            new CriteriaCollection(),
            [],
            [],
            [],
            static fn () => null,
        );

        $adapter = new AnthropicChatAdapter($client, Model::CLAUDE_3_HAIKU->value, 100);
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponse::class, $result);
    }

    public function testHandleRequestMapsToolRoleToUser(): void
    {
        // Anthropic has no "tool" role: a TOOL-role message must be sent as a user message.
        $expectedPayload = [
            'model' => Model::CLAUDE_3_HAIKU->value,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'tool_result',
                            'tool_use_id' => 'toolu_789',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => 'Result from tool execution',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'max_tokens' => 100,
            'system' => '',
        ];

        $mockResponseMatcher = new MockResponseMatcher();
        $mockResponseMatcher->addResponse(
            PartialPayload::create('messages', $expectedPayload),
            new ObjectResponse(DataFixtures::MESSAGES_CREATE_RESPONSE, MetaInformation::empty()),
        );

        $client = new Client(new MockTransport($mockResponseMatcher));

        $request = new AIChatRequest(
            new AIChatMessageCollection(
                new AIChatMessage(
                    AIChatMessageRoleEnum::TOOL,
                    ToolCallPart::create('toolu_789', 'some_tool', 'Result from tool execution'),
                ),
            ),
            new CriteriaCollection(),
            [],
            [],
            [],
            static fn () => null,
        );

        $adapter = new AnthropicChatAdapter($client, Model::CLAUDE_3_HAIKU->value, 100);
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponse::class, $result);
    }

    /**
     * Get the current weather in a given location.
     *
     * @param string $location the location to get the weather for
     * @param int $timestamp timestamp to get the weather
     */
    public function getWeatherMethod(string $location, int $timestamp = 0): string
    {
        return 'Sunny, 72°F in ' . $location;
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

    public function testSupportResponseFormatWithSupportedInstance(): void
    {
        $client = $this->prophesize(ClientInterface::class);
        $supportedFormat = new JsonSchemaResponseFormat(['type' => 'object', 'properties' => []]);

        $adapter = new AnthropicChatAdapter($client->reveal(), Model::CLAUDE_3_SONNET->value);

        $this->assertTrue($adapter->supportsResponseFormat($supportedFormat));
    }

    public function testSupportResponseFormatWithUnsupportedInstance(): void
    {
        $client = $this->prophesize(ClientInterface::class);
        $unsupportedFormat = $this->prophesize(ResponseFormatInterface::class);

        $adapter = new AnthropicChatAdapter($client->reveal(), Model::CLAUDE_3_SONNET->value);

        $this->assertFalse($adapter->supportsResponseFormat($unsupportedFormat->reveal()));
    }
}
