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

namespace ModelflowAi\FireworksAiAdapter\Tests\Unit\Chat;

use ModelflowAi\Chat\Request\AIChatMessageCollection;
use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\Chat\Request\AIChatStreamedRequest;
use ModelflowAi\Chat\Request\Message\AIChatMessage;
use ModelflowAi\Chat\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Chat\Request\ResponseFormat\JsonResponseFormat;
use ModelflowAi\Chat\Response\AIChatResponse;
use ModelflowAi\Chat\Response\AIChatResponseStream;
use ModelflowAi\Chat\ToolInfo\ToolChoiceEnum;
use ModelflowAi\Chat\ToolInfo\ToolInfoBuilder;
use ModelflowAi\Chat\ToolInfo\ToolTypeEnum;
use ModelflowAi\DecisionTree\Criteria\CriteriaCollection;
use ModelflowAi\FireworksAiAdapter\Chat\FireworksAiChatAdapter;
use OpenAI\Contracts\ClientContract;
use OpenAI\Contracts\Resources\ChatContract;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Responses\Chat\CreateStreamedResponse;
use OpenAI\Responses\Meta\MetaInformation;
use OpenAI\Testing\ClientFake;
use OpenAI\Testing\Responses\Fixtures\Chat\CreateResponseFixture;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class FireworksAiChatAdapterTest extends TestCase
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
            'model' => 'accounts/fireworks/models/llama-v3-70b-instruct',
            'messages' => [
                ['role' => 'system', 'content' => [['type' => 'text', 'text' => 'System message']]],
                ['role' => 'user', 'content' => [['type' => 'text', 'text' => 'User message']]],
                ['role' => 'assistant', 'content' => [['type' => 'text', 'text' => 'Assistant message']]],
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

        $request = new AIChatRequest(new AIChatMessageCollection(
            new AIChatMessage(AIChatMessageRoleEnum::SYSTEM, 'System message'),
            new AIChatMessage(AIChatMessageRoleEnum::USER, 'User message'),
            new AIChatMessage(AIChatMessageRoleEnum::ASSISTANT, 'Assistant message'),
        ), new CriteriaCollection(), [], [], [], static fn () => null);

        $adapter = new FireworksAiChatAdapter($client->reveal(), 'accounts/fireworks/models/llama-v3-70b-instruct');
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
            'model' => 'accounts/fireworks/models/llama-v3-70b-instruct',
            'messages' => [
                ['role' => 'system', 'content' => [['type' => 'text', 'text' => 'System message']]],
                ['role' => 'user', 'content' => [['type' => 'text', 'text' => 'User message']]],
                ['role' => 'assistant', 'content' => [['type' => 'text', 'text' => 'Assistant message']]],
            ],
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

        $request = new AIChatRequest(new AIChatMessageCollection(
            new AIChatMessage(AIChatMessageRoleEnum::SYSTEM, 'System message'),
            new AIChatMessage(AIChatMessageRoleEnum::USER, 'User message'),
            new AIChatMessage(AIChatMessageRoleEnum::ASSISTANT, 'Assistant message'),
        ), new CriteriaCollection(), [], [], [
            'seed' => 123,
            'temperature' => 0.5,
        ], static fn () => null);

        $adapter = new FireworksAiChatAdapter($client->reveal(), 'accounts/fireworks/models/llama-v3-70b-instruct');
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
            'model' => 'accounts/fireworks/models/llama-v3-70b-instruct',
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                ['role' => 'system', 'content' => [['type' => 'text', 'text' => 'System message']]],
                ['role' => 'user', 'content' => [['type' => 'text', 'text' => 'User message']]],
                ['role' => 'assistant', 'content' => [['type' => 'text', 'text' => 'Assistant message']]],
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

        $request = new AIChatRequest(new AIChatMessageCollection(
            new AIChatMessage(AIChatMessageRoleEnum::SYSTEM, 'System message'),
            new AIChatMessage(AIChatMessageRoleEnum::USER, 'User message'),
            new AIChatMessage(AIChatMessageRoleEnum::ASSISTANT, 'Assistant message'),
        ), new CriteriaCollection(), [], [], [], static fn () => null, [], new JsonResponseFormat());

        $adapter = new FireworksAiChatAdapter($client->reveal(), 'accounts/fireworks/models/llama-v3-70b-instruct');
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponse::class, $result);
        $this->assertSame(9, $result->getUsage()?->inputTokens);
        $this->assertSame(12, $result->getUsage()->outputTokens);
        $this->assertSame(21, $result->getUsage()->totalTokens);
    }

    public function testHandleRequestStreamed(): void
    {
        /** @var resource $resource */
        $resource = \fopen(__DIR__ . '/resources/stream.txt', 'r');

        $client = new ClientFake([
            CreateStreamedResponse::fake($resource),
        ]);

        $request = new AIChatStreamedRequest(new AIChatMessageCollection(
            new AIChatMessage(AIChatMessageRoleEnum::SYSTEM, 'System message'),
            new AIChatMessage(AIChatMessageRoleEnum::USER, 'User message'),
            new AIChatMessage(AIChatMessageRoleEnum::ASSISTANT, 'Assistant message'),
        ), new CriteriaCollection(), [], [], [], static fn () => null);

        $adapter = new FireworksAiChatAdapter($client);
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

        $request = new AIChatRequest(new AIChatMessageCollection(
            new AIChatMessage(AIChatMessageRoleEnum::SYSTEM, 'System message'),
            new AIChatMessage(AIChatMessageRoleEnum::USER, 'User message'),
            new AIChatMessage(AIChatMessageRoleEnum::ASSISTANT, 'Assistant message'),
        ), new CriteriaCollection(), [
            'test' => [$this, 'toolMethod'],
        ], [
            ToolInfoBuilder::buildToolInfo($this, 'toolMethod', 'test'),
        ], [], static fn () => null, [], null, ToolChoiceEnum::AUTO);

        $adapter = new FireworksAiChatAdapter($client);
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
        /** @var resource $resource */
        $resource = \fopen(__DIR__ . '/resources/tools-stream.txt', 'r');

        $client = new ClientFake([
            CreateStreamedResponse::fake($resource),
        ]);

        $request = new AIChatStreamedRequest(new AIChatMessageCollection(
            new AIChatMessage(AIChatMessageRoleEnum::SYSTEM, 'System message'),
            new AIChatMessage(AIChatMessageRoleEnum::USER, 'User message'),
            new AIChatMessage(AIChatMessageRoleEnum::ASSISTANT, 'Assistant message'),
        ), new CriteriaCollection(), [
            'test' => [$this, 'toolMethod'],
        ], [
            ToolInfoBuilder::buildToolInfo($this, 'toolMethod', 'test'),
        ], [], static fn () => null);

        $adapter = new FireworksAiChatAdapter($client);
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
