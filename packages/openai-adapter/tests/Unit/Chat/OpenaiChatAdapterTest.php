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

namespace ModelflowAi\OpenaiAdapter\Tests\Unit\Chat;

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
use ModelflowAi\Chat\Request\ResponseFormat\ResponseFormatInterface;
use ModelflowAi\Chat\Response\AIChatResponse;
use ModelflowAi\Chat\Response\AIChatResponseStream;
use ModelflowAi\Chat\Response\AIChatToolCall;
use ModelflowAi\Chat\Response\Usage;
use ModelflowAi\Chat\Response\UsageCallbackInterface;
use ModelflowAi\Chat\ToolInfo\ToolChoiceEnum;
use ModelflowAi\Chat\ToolInfo\ToolInfoBuilder;
use ModelflowAi\Chat\ToolInfo\ToolTypeEnum;
use ModelflowAi\DecisionTree\Criteria\CriteriaCollection;
use ModelflowAi\OpenaiAdapter\Chat\OpenaiChatAdapter;
use OpenAI\Contracts\ClientContract;
use OpenAI\Contracts\Resources\ChatContract;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Responses\Chat\CreateStreamedResponse;
use OpenAI\Responses\Meta\MetaInformation;
use OpenAI\Testing\ClientFake;
use OpenAI\Testing\Responses\Fixtures\Chat\CreateResponseFixture;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class OpenaiChatAdapterTest extends TestCase
{
    use ProphecyTrait;

    public function testHandleRequest(): void
    {
        $chat = $this->prophesize(ChatContract::class);
        $client = $this->prophesize(ClientContract::class);
        $client->chat()->willReturn($chat->reveal());

        $attributes = CreateResponseFixture::ATTRIBUTES;
        $attributes['system_fingerprint'] = '123-123-123';

        $chat->create([
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'system', 'content' => 'System message'],
                ['role' => 'user', 'content' => 'User message'],
                ['role' => 'assistant', 'content' => 'Assistant message'],
            ],
        ])->willReturn(CreateResponse::from(
            $attributes,
            MetaInformation::from([
                'x-request-id' => ['123'],
                'openai-model' => ['gpt-4'],
                'openai-organization' => ['org'],
                'openai-version' => ['2021-10-10'],
                'openai-processing-ms' => ['123'],
                'x-ratelimit-limit-requests' => ['123'],
                'x-ratelimit-limit-tokens' => ['123'],
                'x-ratelimit-remaining-requests' => ['123'],
                'x-ratelimit-remaining-tokens' => ['123'],
                'x-ratelimit-reset-requests' => ['123'],
                'x-ratelimit-reset-tokens' => ['123'],
            ]),
        ));

        $request = new AIChatRequest(
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

        $adapter = new OpenaiChatAdapter($client->reveal());
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponse::class, $result);
        $this->assertSame(AIChatMessageRoleEnum::ASSISTANT, $result->getMessage()->role);
        $this->assertSame("\n\nHello there, this is a fake chat response.", $result->getMessage()->content);
        $this->assertSame(9, $result->getUsage()?->inputTokens);
        $this->assertSame(12, $result->getUsage()->outputTokens);
        $this->assertSame(21, $result->getUsage()->totalTokens);
    }

    public function testHandleRequestWithMultipleParts(): void
    {
        $chat = $this->prophesize(ChatContract::class);
        $client = $this->prophesize(ClientContract::class);
        $client->chat()->willReturn($chat->reveal());

        $attributes = CreateResponseFixture::ATTRIBUTES;
        $attributes['system_fingerprint'] = '123-123-123';

        $chat->create([
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'System message',
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => \sprintf('data:%s;base64,%s', 'image/jpeg', 'base64'),
                            ],
                        ],
                        'Test result 1',
                    ],
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
                    'tool_call_id' => 'call_1Ue9UPErEy4dz56T3znEoBO1',
                    'name' => 'test',
                ],
            ],
        ])->willReturn(CreateResponse::from(
            $attributes,
            MetaInformation::from([
                'x-request-id' => ['123'],
                'openai-model' => ['gpt-4'],
                'openai-organization' => ['org'],
                'openai-version' => ['2021-10-10'],
                'openai-processing-ms' => ['123'],
                'x-ratelimit-limit-requests' => ['123'],
                'x-ratelimit-limit-tokens' => ['123'],
                'x-ratelimit-remaining-requests' => ['123'],
                'x-ratelimit-remaining-tokens' => ['123'],
                'x-ratelimit-reset-requests' => ['123'],
                'x-ratelimit-reset-tokens' => ['123'],
            ]),
        ));

        $request = new AIChatRequest(
            new AIChatMessageCollection(
                new AIChatMessage(AIChatMessageRoleEnum::SYSTEM, [
                    TextPart::create('System message'),
                    new ImageBase64Part('base64', 'image/jpeg'),
                    ToolCallsPart::create([
                        new AIChatToolCall(ToolTypeEnum::FUNCTION, 'call_1Ue9UPErEy4dz56T3znEoBO1', 'test', [
                            'required' => 'Test required 1',
                            'optional' => 'Test optional 1',
                        ]),
                    ]),
                    ToolCallPart::create('call_1Ue9UPErEy4dz56T3znEoBO1', 'test', 'Test result 1'),
                ]),
            ),
            new CriteriaCollection(),
            [],
            [],
            [],
            static fn () => null,
        );

        $adapter = new OpenaiChatAdapter($client->reveal());
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponse::class, $result);
        $this->assertSame(AIChatMessageRoleEnum::ASSISTANT, $result->getMessage()->role);
        $this->assertSame("\n\nHello there, this is a fake chat response.", $result->getMessage()->content);
        $this->assertSame(9, $result->getUsage()?->inputTokens);
        $this->assertSame(12, $result->getUsage()->outputTokens);
        $this->assertSame(21, $result->getUsage()->totalTokens);
    }

    public function testHandleRequestWithOptions(): void
    {
        $chat = $this->prophesize(ChatContract::class);
        $client = $this->prophesize(ClientContract::class);
        $client->chat()->willReturn($chat->reveal());

        $attributes = CreateResponseFixture::ATTRIBUTES;
        $attributes['system_fingerprint'] = '123-123-123';

        $chat->create([
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'system', 'content' => 'System message'],
                ['role' => 'user', 'content' => 'User message'],
                ['role' => 'assistant', 'content' => 'Assistant message'],
            ],
            'seed' => 123,
            'temperature' => 0.5,
        ])->willReturn(CreateResponse::from(
            $attributes,
            MetaInformation::from([
                'x-request-id' => ['123'],
                'openai-model' => ['gpt-4'],
                'openai-organization' => ['org'],
                'openai-version' => ['2021-10-10'],
                'openai-processing-ms' => ['123'],
                'x-ratelimit-limit-requests' => ['123'],
                'x-ratelimit-limit-tokens' => ['123'],
                'x-ratelimit-remaining-requests' => ['123'],
                'x-ratelimit-remaining-tokens' => ['123'],
                'x-ratelimit-reset-requests' => ['123'],
                'x-ratelimit-reset-tokens' => ['123'],
            ]),
        ));

        $request = new AIChatRequest(
            new AIChatMessageCollection(
                new AIChatMessage(AIChatMessageRoleEnum::SYSTEM, 'System message'),
                new AIChatMessage(AIChatMessageRoleEnum::USER, 'User message'),
                new AIChatMessage(AIChatMessageRoleEnum::ASSISTANT, 'Assistant message'),
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

        $adapter = new OpenaiChatAdapter($client->reveal());
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponse::class, $result);
        $this->assertSame(AIChatMessageRoleEnum::ASSISTANT, $result->getMessage()->role);
        $this->assertSame("\n\nHello there, this is a fake chat response.", $result->getMessage()->content);
    }

    public function testHandleRequestAsJson(): void
    {
        $chat = $this->prophesize(ChatContract::class);
        $client = $this->prophesize(ClientContract::class);
        $client->chat()->willReturn($chat->reveal());

        $attributes = CreateResponseFixture::ATTRIBUTES;
        $attributes['system_fingerprint'] = '123-123-123';

        $chat->create([
            'model' => 'gpt-4',
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                ['role' => 'system', 'content' => 'System message'],
                ['role' => 'user', 'content' => 'User message'],
                ['role' => 'assistant', 'content' => 'Assistant message'],
            ],
        ])->willReturn(CreateResponse::from(
            $attributes,
            MetaInformation::from([
                'x-request-id' => ['123'],
                'openai-model' => ['gpt-4'],
                'openai-organization' => ['org'],
                'openai-version' => ['2021-10-10'],
                'openai-processing-ms' => ['123'],
                'x-ratelimit-limit-requests' => ['123'],
                'x-ratelimit-limit-tokens' => ['123'],
                'x-ratelimit-remaining-requests' => ['123'],
                'x-ratelimit-remaining-tokens' => ['123'],
                'x-ratelimit-reset-requests' => ['123'],
                'x-ratelimit-reset-tokens' => ['123'],
            ]),
        ));

        $request = new AIChatRequest(
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
            [],
            new JsonResponseFormat(),
        );

        $adapter = new OpenaiChatAdapter($client->reveal());
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponse::class, $result);
        $this->assertSame(9, $result->getUsage()?->inputTokens);
        $this->assertSame(12, $result->getUsage()->outputTokens);
        $this->assertSame(21, $result->getUsage()->totalTokens);
    }

    public function testHandleRequestAsJsonWithResponseFormat(): void
    {
        $chat = $this->prophesize(ChatContract::class);
        $client = $this->prophesize(ClientContract::class);
        $client->chat()->willReturn($chat->reveal());

        $attributes = CreateResponseFixture::ATTRIBUTES;
        $attributes['system_fingerprint'] = '123-123-123';

        $chat->create([
            'model' => 'gpt-4',
            'response_format' => ['type' => 'json_schema', 'json_schema' => [
                'name' => 'response',
                'strict' => true,
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'dummy' => [
                            'type' => 'string',
                            'description' => '',
                        ],
                    ],
                    'description' => '',
                    'additionalProperties' => false,
                ],
            ], ],
            'messages' => [
                ['role' => 'system', 'content' => 'System message'],
                ['role' => 'user', 'content' => 'User message'],
                ['role' => 'assistant', 'content' => 'Assistant message'],
            ],
        ])->willReturn(CreateResponse::from(
            $attributes,
            MetaInformation::from([
                'x-request-id' => ['123'],
                'openai-model' => ['gpt-4'],
                'openai-organization' => ['org'],
                'openai-version' => ['2021-10-10'],
                'openai-processing-ms' => ['123'],
                'x-ratelimit-limit-requests' => ['123'],
                'x-ratelimit-limit-tokens' => ['123'],
                'x-ratelimit-remaining-requests' => ['123'],
                'x-ratelimit-remaining-tokens' => ['123'],
                'x-ratelimit-reset-requests' => ['123'],
                'x-ratelimit-reset-tokens' => ['123'],
            ]),
        ));

        $request = new AIChatRequest(
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
            [],
            new JsonSchemaResponseFormat([
                'type' => 'object',
                'properties' => [
                    'dummy' => ['type' => 'string'],
                ],
            ]),
        );

        $adapter = new OpenaiChatAdapter($client->reveal());
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponse::class, $result);
    }

    public function testHandleRequestStreamed(): void
    {
        /** @var resource $resource */
        $resource = \fopen(__DIR__ . '/resources/stream.txt', 'r');
        $client = new ClientFake([
            CreateStreamedResponse::fake($resource),
        ]);

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

        $adapter = new OpenaiChatAdapter($client);
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
        $contents = (array) \json_decode((string) \file_get_contents(__DIR__ . '/resources/tools.txt'), true);

        $client = new ClientFake([
            CreateResponse::fake($contents),
        ]);

        $request = new AIChatRequest(
            new AIChatMessageCollection(
                new AIChatMessage(AIChatMessageRoleEnum::SYSTEM, 'System message'),
                new AIChatMessage(AIChatMessageRoleEnum::USER, 'User message'),
                new AIChatMessage(AIChatMessageRoleEnum::ASSISTANT, 'Assistant message'),
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
            [],
            null,
            ToolChoiceEnum::NONE,
        );

        $adapter = new OpenaiChatAdapter($client);
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

        $this->assertSame(73, $result->getUsage()?->inputTokens);
        $this->assertSame(24, $result->getUsage()->outputTokens);
        $this->assertSame(97, $result->getUsage()->totalTokens);
    }

    public function testHandleRequestStreamedWithTools(): void
    {
        /** @var resource $resource */
        $resource = \fopen(__DIR__ . '/resources/tools-stream.txt', 'r');
        $client = new ClientFake([
            CreateStreamedResponse::fake($resource),
        ]);

        $request = new AIChatStreamedRequest(
            new AIChatMessageCollection(
                new AIChatMessage(AIChatMessageRoleEnum::SYSTEM, 'System message'),
                new AIChatMessage(AIChatMessageRoleEnum::USER, 'User message'),
                new AIChatMessage(AIChatMessageRoleEnum::ASSISTANT, 'Assistant message'),
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

        $adapter = new OpenaiChatAdapter($client);
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

    public function testSupportResponseFormatWithSupportedInstance(): void
    {
        $client = $this->createMock(ClientContract::class);
        $unsupportedFormat = $this->createMock(JsonSchemaResponseFormat::class);

        $adapter = new OpenaiChatAdapter($client);

        $this->assertTrue($adapter->supportsResponseFormat($unsupportedFormat));
    }

    public function testSupportResponseFormatWithUnsupportedInstance(): void
    {
        $client = $this->createMock(ClientContract::class);
        $unsupportedFormat = $this->createMock(ResponseFormatInterface::class);

        $adapter = new OpenaiChatAdapter($client);

        $this->assertFalse($adapter->supportsResponseFormat($unsupportedFormat));
    }

    public function testHandleRequestStreamedWithUsage(): void
    {
        /** @var resource $resource */
        $resource = \fopen(__DIR__ . '/resources/stream-with-usage.txt', 'r');
        $client = new ClientFake([
            CreateStreamedResponse::fake($resource),
        ]);

        $request = new AIChatStreamedRequest(
            new AIChatMessageCollection(
                new AIChatMessage(AIChatMessageRoleEnum::SYSTEM, 'System message'),
                new AIChatMessage(AIChatMessageRoleEnum::USER, 'User message'),
            ),
            new CriteriaCollection(),
            [],
            [],
            [],
            static fn () => null,
        );

        $adapter = new OpenaiChatAdapter($client);
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponseStream::class, $result);

        // Register callback to track usage updates
        $callback = new class implements UsageCallbackInterface {
            /** @var list<array{inputTokens:int, outputTokens:int, totalTokens:int, isFinal:bool}> */
            public array $updates = [];

            public function onUsageUpdate(Usage $usage, bool $isFinal): void
            {
                $this->updates[] = [
                    'inputTokens' => $usage->inputTokens,
                    'outputTokens' => $usage->outputTokens,
                    'totalTokens' => $usage->totalTokens,
                    'isFinal' => $isFinal,
                ];
            }
        };
        $result->registerUsageCallback($callback);

        // Consume the stream
        $contents = ['Lorem', 'Ipsum'];
        foreach ($result->getMessageStream() as $i => $response) {
            $this->assertSame(AIChatMessageRoleEnum::ASSISTANT, $response->role);
            $this->assertSame($contents[$i], $response->content);
        }

        // Verify usage was received
        $this->assertCount(1, $callback->updates);
        $this->assertSame(10, $callback->updates[0]['inputTokens']);
        $this->assertSame(20, $callback->updates[0]['outputTokens']);
        $this->assertSame(30, $callback->updates[0]['totalTokens']);
        $this->assertTrue($callback->updates[0]['isFinal']);

        // Verify getUsage() returns the same data
        $usage = $result->getUsage();
        $this->assertNotNull($usage);
        $this->assertSame(10, $usage->inputTokens);
        $this->assertSame(20, $usage->outputTokens);
        $this->assertSame(30, $usage->totalTokens);
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
