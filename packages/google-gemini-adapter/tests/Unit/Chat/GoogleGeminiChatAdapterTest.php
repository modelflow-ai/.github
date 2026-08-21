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

namespace ModelflowAi\GoogleGeminiAdapter\Tests\Unit\Chat;

use Gemini\Contracts\ClientContract;
use Gemini\Data\Content;
use Gemini\Data\FunctionCall;
use Gemini\Data\FunctionResponse;
use Gemini\Data\GenerationConfig;
use Gemini\Data\ThinkingConfig;
use Gemini\Data\Tool;
use Gemini\Enums\DataType;
use Gemini\Enums\ModelType;
use Gemini\Enums\ResponseMimeType;
use Gemini\Enums\Role;
use Gemini\Enums\ThinkingLevel;
use Gemini\Responses\GenerativeModel\GenerateContentResponse;
use Gemini\Testing\ClientFake;
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
use ModelflowAi\Chat\ToolInfo\Parameter;
use ModelflowAi\Chat\ToolInfo\ToolInfo;
use ModelflowAi\Chat\ToolInfo\ToolTypeEnum;
use ModelflowAi\DecisionTree\Criteria\CriteriaCollection;
use ModelflowAi\GoogleGeminiAdapter\Chat\GoogleGeminiChatAdapter;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class GoogleGeminiChatAdapterTest extends TestCase
{
    use ProphecyTrait;

    public function testSupports(): void
    {
        $client = $this->prophesize(ClientContract::class);

        $adapter = new GoogleGeminiChatAdapter($client->reveal(), ModelType::GEMINI_FLASH->value);

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

    public function testHandleRequest(): void
    {
        $client = new ClientFake([
            GenerateContentResponse::fake([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => 'success',
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $request = new AIChatRequest(
            new AIChatMessageCollection(
                new AIChatMessage(
                    AIChatMessageRoleEnum::SYSTEM,
                    'Hello',
                ),
                new AIChatMessage(
                    AIChatMessageRoleEnum::USER,
                    'World!',
                ),
            ),
            new CriteriaCollection(),
            [],
            [],
            [],
            static fn () => null,
        );

        $adapter = new GoogleGeminiChatAdapter($client, ModelType::GEMINI_FLASH->value);
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponse::class, $result);
        $this->assertSame(AIChatMessageRoleEnum::ASSISTANT, $result->getMessage()->role);
        $this->assertSame('success', $result->getMessage()->content);

        $client->generativeModel(ModelType::GEMINI_FLASH->value)
            ->assertSent(
                static fn (string $methods, array $args) => 'generateContent' === $methods
                    && 'Hello' === $args[0]->parts[0]->text
                    && 'World!' === $args[1]->parts[0]->text,
            );
    }

    public function testHandleRequestWithOptions(): void
    {
        $client = new ClientFake([
            GenerateContentResponse::fake([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => 'success',
                                ],
                            ],
                        ],
                    ],
                ],
                'usageMetadata' => [
                    'promptTokenCount' => 150,
                    'totalTokenCount' => 200,
                ],
            ]),
        ]);

        $request = new AIChatRequest(new AIChatMessageCollection(
            new AIChatMessage(
                AIChatMessageRoleEnum::SYSTEM,
                'Hello',
            ),
            new AIChatMessage(
                AIChatMessageRoleEnum::USER,
                'World!',
            ),
        ), new CriteriaCollection(), [], [], [
            'temperature' => 0.5,
        ], static fn () => null);

        $adapter = new GoogleGeminiChatAdapter($client, ModelType::GEMINI_FLASH->value);
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponse::class, $result);
        $this->assertSame(AIChatMessageRoleEnum::ASSISTANT, $result->getMessage()->role);
        $this->assertSame('success', $result->getMessage()->content);
        $this->assertSame(150, $result->getUsage()?->inputTokens);
        $this->assertSame(50, $result->getUsage()->outputTokens);
        $this->assertSame(200, $result->getUsage()->totalTokens);

        $client->generativeModel(ModelType::GEMINI_FLASH->value)
            ->assertSent(
                static fn (string $methods, array $args) => 'generateContent' === $methods
                    && 'Hello' === $args[0]->parts[0]->text
                    && 'World!' === $args[1]->parts[0]->text,
            );
    }

    public function testHandleRequestStreamed(): void
    {
        $client = new ClientFake([
            GenerateContentResponse::fakeStream(\fopen(__DIR__ . '/Fixtures/stream.json', 'r')), // @phpstan-ignore-line
        ]);

        $request = new AIChatStreamedRequest(new AIChatMessageCollection(
            new AIChatMessage(
                AIChatMessageRoleEnum::SYSTEM,
                'Hello',
            ),
            new AIChatMessage(
                AIChatMessageRoleEnum::USER,
                'World!',
            ),
        ), new CriteriaCollection(), [], [], [], static fn () => null);

        $adapter = new GoogleGeminiChatAdapter($client, ModelType::GEMINI_FLASH->value);
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponseStream::class, $result);
        $contents = ['Hello', '!'];
        foreach ($result->getMessageStream() as $i => $response) {
            $this->assertSame(AIChatMessageRoleEnum::ASSISTANT, $response->role);
            $this->assertSame($contents[$i], $response->content);
        }
    }

    public function testHandleRequestWithJsonSchemaResponseFormat(): void
    {
        $client = new ClientFake([
            GenerateContentResponse::fake([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => '{"name":"test"}',
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $schema = [
            'type' => 'object',
            'properties' => [
                'name' => [
                    'type' => 'string',
                    'description' => 'The name',
                ],
            ],
            'required' => ['name'],
        ];

        $request = new AIChatRequest(
            new AIChatMessageCollection(
                new AIChatMessage(AIChatMessageRoleEnum::USER, 'Return a name'),
            ),
            new CriteriaCollection(),
            [],
            [],
            [],
            static fn () => null,
            [],
            new JsonSchemaResponseFormat($schema),
        );

        $adapter = new GoogleGeminiChatAdapter($client, ModelType::GEMINI_FLASH->value);
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponse::class, $result);
        $this->assertSame('{"name":"test"}', $result->getMessage()->content);

        $client->generativeModel(ModelType::GEMINI_FLASH->value)
            ->assertFunctionCalled(
                static fn (string $method, array $args) => 'withGenerationConfig' === $method
                    && $args[0] instanceof GenerationConfig
                    && ResponseMimeType::APPLICATION_JSON === $args[0]->responseMimeType
                    && $args[0]->responseSchema instanceof \Gemini\Data\Schema
                    && DataType::OBJECT === $args[0]->responseSchema->type,
            );
    }

    public function testHandleRequestWithJsonResponseFormat(): void
    {
        $client = new ClientFake([
            GenerateContentResponse::fake([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => '["a","b"]',
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $request = new AIChatRequest(
            new AIChatMessageCollection(
                new AIChatMessage(AIChatMessageRoleEnum::USER, 'Return a list'),
            ),
            new CriteriaCollection(),
            [],
            [],
            [],
            static fn () => null,
            [],
            new JsonResponseFormat(),
        );

        $adapter = new GoogleGeminiChatAdapter($client, ModelType::GEMINI_FLASH->value);
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponse::class, $result);

        $client->generativeModel(ModelType::GEMINI_FLASH->value)
            ->assertFunctionCalled(
                static fn (string $method, array $args) => 'withGenerationConfig' === $method
                    && $args[0] instanceof GenerationConfig
                    && ResponseMimeType::APPLICATION_JSON === $args[0]->responseMimeType
                    && !$args[0]->responseSchema instanceof \Gemini\Data\Schema,
            );
    }

    public function testSupportsResponseFormatWithJsonSchema(): void
    {
        $client = $this->prophesize(ClientContract::class);
        $adapter = new GoogleGeminiChatAdapter($client->reveal(), ModelType::GEMINI_FLASH->value);

        $format = new JsonSchemaResponseFormat(['type' => 'object', 'properties' => []]);
        $this->assertTrue($adapter->supportsResponseFormat($format));
    }

    public function testSupportsResponseFormatWithJsonResponseFormat(): void
    {
        $client = $this->prophesize(ClientContract::class);
        $adapter = new GoogleGeminiChatAdapter($client->reveal(), ModelType::GEMINI_FLASH->value);

        $format = new JsonResponseFormat();
        $this->assertTrue($adapter->supportsResponseFormat($format));
    }

    public function testSupportsResponseFormatWithUnsupportedFormat(): void
    {
        $client = $this->prophesize(ClientContract::class);
        $adapter = new GoogleGeminiChatAdapter($client->reveal(), ModelType::GEMINI_FLASH->value);

        $format = $this->prophesize(ResponseFormatInterface::class);
        $this->assertFalse($adapter->supportsResponseFormat($format->reveal()));
    }

    public function testHandleRequestWithToolCallResponse(): void
    {
        $client = new ClientFake([
            GenerateContentResponse::fake([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                // Blank the fixture text part, then append the function call as a new part.
                                ['text' => ''],
                                [
                                    'functionCall' => [
                                        'name' => 'get_weather',
                                        'args' => ['location' => 'Berlin'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $request = new AIChatRequest(
            new AIChatMessageCollection(
                new AIChatMessage(AIChatMessageRoleEnum::USER, 'What is the weather in Berlin?'),
            ),
            new CriteriaCollection(),
            [],
            [
                new ToolInfo(
                    ToolTypeEnum::FUNCTION,
                    'get_weather',
                    'Get the current weather',
                    [new Parameter('location', 'string', 'The location')],
                    [new Parameter('location', 'string', 'The location')],
                ),
            ],
            [],
            static fn () => null,
        );

        $adapter = new GoogleGeminiChatAdapter($client, ModelType::GEMINI_FLASH->value);
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponse::class, $result);
        $this->assertSame('', $result->getMessage()->content);

        $toolCalls = $result->getMessage()->toolCalls;
        $this->assertNotNull($toolCalls);
        $this->assertCount(1, $toolCalls);
        $this->assertSame(ToolTypeEnum::FUNCTION, $toolCalls[0]->type);
        $this->assertSame('get_weather', $toolCalls[0]->name);
        $this->assertSame('get_weather', $toolCalls[0]->id);
        $this->assertSame(['location' => 'Berlin'], $toolCalls[0]->arguments);

        $client->generativeModel(ModelType::GEMINI_FLASH->value)
            ->assertFunctionCalled(
                static fn (string $method, array $args): bool => 'withTool' === $method
                    && $args[0] instanceof Tool
                    && null !== $args[0]->functionDeclarations
                    && 'get_weather' === $args[0]->functionDeclarations[0]->name,
            );
    }

    public function testHandleRequestKeepsThoughtSignatureOfToolCall(): void
    {
        $client = new ClientFake([
            GenerateContentResponse::fake([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => ''],
                                [
                                    'functionCall' => [
                                        'name' => 'get_weather',
                                        'args' => ['location' => 'Berlin'],
                                    ],
                                    'thoughtSignature' => 'signature-123',
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $request = new AIChatRequest(
            new AIChatMessageCollection(
                new AIChatMessage(AIChatMessageRoleEnum::USER, 'What is the weather in Berlin?'),
            ),
            new CriteriaCollection(),
            [],
            [
                new ToolInfo(
                    ToolTypeEnum::FUNCTION,
                    'get_weather',
                    'Get the current weather',
                    [new Parameter('location', 'string', 'The location')],
                    [new Parameter('location', 'string', 'The location')],
                ),
            ],
            [],
            static fn () => null,
        );

        $adapter = new GoogleGeminiChatAdapter($client, ModelType::GEMINI_FLASH->value);
        $result = $adapter->handleRequest($request);

        $toolCalls = $result->getMessage()->toolCalls;
        $this->assertNotNull($toolCalls);
        $this->assertSame('signature-123', $toolCalls[0]->signature);
    }

    public function testHandleRequestSendsThoughtSignatureBackWithToolCall(): void
    {
        $client = new ClientFake([
            GenerateContentResponse::fake([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => 'It is sunny.',
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $request = new AIChatRequest(
            new AIChatMessageCollection(
                new AIChatMessage(AIChatMessageRoleEnum::USER, 'What is the weather in Berlin?'),
                new AIChatMessage(
                    AIChatMessageRoleEnum::ASSISTANT,
                    ToolCallsPart::create([
                        new AIChatToolCall(
                            ToolTypeEnum::FUNCTION,
                            'call_1',
                            'get_weather',
                            ['location' => 'Berlin'],
                            'signature-123',
                        ),
                    ]),
                ),
                new AIChatMessage(
                    AIChatMessageRoleEnum::TOOL,
                    ToolCallPart::create('call_1', 'get_weather', '{"temperature":21}'),
                ),
            ),
            new CriteriaCollection(),
            [],
            [],
            [],
            static fn () => null,
        );

        $adapter = new GoogleGeminiChatAdapter($client, ModelType::GEMINI_FLASH->value);
        $adapter->handleRequest($request);

        $client->generativeModel(ModelType::GEMINI_FLASH->value)
            ->assertSent(
                static function (string $method, array $args): bool {
                    $content = $args[1] ?? null;

                    return 'generateContent' === $method
                        && $content instanceof Content
                        && 'signature-123' === $content->parts[0]->thoughtSignature;
                },
            );
    }

    public function testHandleRequestSerializesToolCallAndToolResultMessages(): void
    {
        $client = new ClientFake([
            GenerateContentResponse::fake([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => 'It is sunny.',
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $request = new AIChatRequest(
            new AIChatMessageCollection(
                new AIChatMessage(AIChatMessageRoleEnum::USER, 'What is the weather in Berlin?'),
                new AIChatMessage(
                    AIChatMessageRoleEnum::ASSISTANT,
                    ToolCallsPart::create([
                        new AIChatToolCall(ToolTypeEnum::FUNCTION, 'call_1', 'get_weather', ['location' => 'Berlin']),
                    ]),
                ),
                new AIChatMessage(
                    AIChatMessageRoleEnum::TOOL,
                    ToolCallPart::create('call_1', 'get_weather', '{"temperature":21}'),
                ),
            ),
            new CriteriaCollection(),
            [],
            [],
            [],
            static fn () => null,
        );

        $adapter = new GoogleGeminiChatAdapter($client, ModelType::GEMINI_FLASH->value);
        $result = $adapter->handleRequest($request);

        $this->assertSame('It is sunny.', $result->getMessage()->content);

        $client->generativeModel(ModelType::GEMINI_FLASH->value)
            ->assertSent(
                static function (string $method, array $args): bool {
                    if ('generateContent' !== $method) {
                        return false;
                    }

                    $functionCall = $args[1]->parts[0]->functionCall;
                    $functionResponse = $args[2]->parts[0]->functionResponse;

                    return $functionCall instanceof FunctionCall
                        && 'get_weather' === $functionCall->name
                        && ['location' => 'Berlin'] === $functionCall->args
                        && 'call_1' === $functionCall->id
                        && Role::MODEL === $args[1]->role
                        && $functionResponse instanceof FunctionResponse
                        && 'get_weather' === $functionResponse->name
                        && ['temperature' => 21] === $functionResponse->response
                        && Role::USER === $args[2]->role;
                },
            );
    }

    public function testHandleRequestAsksForTheLowestThinkingLevel(): void
    {
        $client = $this->createResponseFake();
        $adapter = new GoogleGeminiChatAdapter($client, 'gemini-3.7-flash');

        $adapter->handleRequest($this->createRequest());

        $client->generativeModel('gemini-3.7-flash')
            ->assertFunctionCalled(
                static fn (string $method, array $args) => 'withGenerationConfig' === $method
                    && $args[0] instanceof GenerationConfig
                    && $args[0]->thinkingConfig instanceof ThinkingConfig
                    && ThinkingLevel::LOW === $args[0]->thinkingConfig->thinkingLevel
                    && false === $args[0]->thinkingConfig->includeThoughts,
            );
    }

    public function testHandleRequestSendsTheConfiguredThinkingLevel(): void
    {
        $client = $this->createResponseFake();
        $adapter = new GoogleGeminiChatAdapter($client, 'gemini-3.7-flash', ThinkingLevel::HIGH);

        $adapter->handleRequest($this->createRequest());

        $client->generativeModel('gemini-3.7-flash')
            ->assertFunctionCalled(
                static fn (string $method, array $args) => 'withGenerationConfig' === $method
                    && $args[0] instanceof GenerationConfig
                    && $args[0]->thinkingConfig instanceof ThinkingConfig
                    && ThinkingLevel::HIGH === $args[0]->thinkingConfig->thinkingLevel,
            );
    }

    public function testHandleRequestOmitsThinkingForOlderGenerations(): void
    {
        $client = $this->createResponseFake();
        $adapter = new GoogleGeminiChatAdapter($client, ModelType::GEMINI_FLASH->value);

        $adapter->handleRequest($this->createRequest());

        $client->generativeModel(ModelType::GEMINI_FLASH->value)
            ->assertFunctionCalled(
                static fn (string $method, array $args) => 'withGenerationConfig' === $method
                    && $args[0] instanceof GenerationConfig
                    && !$args[0]->thinkingConfig instanceof ThinkingConfig,
            );
    }

    private function createResponseFake(): ClientFake
    {
        return new ClientFake([
            GenerateContentResponse::fake([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => 'success',
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);
    }

    private function createRequest(): AIChatRequest
    {
        return new AIChatRequest(
            new AIChatMessageCollection(
                new AIChatMessage(AIChatMessageRoleEnum::USER, 'Hello'),
            ),
            new CriteriaCollection(),
            [],
            [],
            [],
            static fn () => null,
        );
    }
}
