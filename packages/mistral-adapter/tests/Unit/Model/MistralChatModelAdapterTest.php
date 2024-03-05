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

namespace ModelflowAi\MistralAdapter\Tests\Unit\Model;

use ModelflowAi\ApiClient\Responses\MetaInformation;
use ModelflowAi\Core\Request\AIChatMessageCollection;
use ModelflowAi\Core\Request\AIChatRequest;
use ModelflowAi\Core\Request\Criteria\AIRequestCriteriaCollection;
use ModelflowAi\Core\Request\Message\AIChatMessage;
use ModelflowAi\Core\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Core\Response\AIChatResponse;
use ModelflowAi\Core\Response\AIChatResponseStream;
use ModelflowAi\Mistral\ClientInterface;
use ModelflowAi\Mistral\Model;
use ModelflowAi\Mistral\Resources\ChatInterface;
use ModelflowAi\Mistral\Responses\Chat\CreateResponse;
use ModelflowAi\Mistral\Responses\Chat\CreateStreamedResponse;
use ModelflowAi\MistralAdapter\Model\MistralChatModelAdapter;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class MistralChatModelAdapterTest extends TestCase
{
    use ProphecyTrait;

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
        ])->willReturn(CreateResponse::from([
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
        ], MetaInformation::from([])));

        $request = new AIChatRequest(new AIChatMessageCollection(
            new AIChatMessage(AIChatMessageRoleEnum::USER, 'some text'),
        ), new AIRequestCriteriaCollection(), [], fn () => null);

        $adapter = new MistralChatModelAdapter($client->reveal());
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
        ])->willReturn(CreateResponse::from([
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
        ], MetaInformation::from([])));

        $request = new AIChatRequest(new AIChatMessageCollection(
            new AIChatMessage(AIChatMessageRoleEnum::USER, 'some text'),
        ), new AIRequestCriteriaCollection(), ['format' => 'json'], fn () => null);

        $adapter = new MistralChatModelAdapter($client->reveal());
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
        ])->willReturn(CreateResponse::from([
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
                'total_tokens' => 336,
            ],
        ], MetaInformation::from([])));

        $request = new AIChatRequest(new AIChatMessageCollection(
            new AIChatMessage(AIChatMessageRoleEnum::USER, 'some text'),
        ), new AIRequestCriteriaCollection(), ['format' => 'json'], fn () => null);

        $adapter = new MistralChatModelAdapter($client->reveal(), Model::LARGE);
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponse::class, $result);
        $this->assertSame(AIChatMessageRoleEnum::ASSISTANT, $result->getMessage()->role);
        $this->assertSame('{"message": "Lorem Ipsum"}', $result->getMessage()->content);
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
        ])->willReturn(new \ArrayIterator([
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
        ], ));

        $request = new AIChatRequest(new AIChatMessageCollection(
            new AIChatMessage(AIChatMessageRoleEnum::SYSTEM, 'System message'),
            new AIChatMessage(AIChatMessageRoleEnum::USER, 'User message'),
            new AIChatMessage(AIChatMessageRoleEnum::ASSISTANT, 'Assistant message'),
        ), new AIRequestCriteriaCollection(), ['streamed' => true], fn () => null);

        $adapter = new MistralChatModelAdapter($client->reveal());
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponseStream::class, $result);
        $contents = ['Lorem', 'Ipsum'];
        foreach ($result->getMessageStream() as $i => $response) {
            $this->assertSame(AIChatMessageRoleEnum::ASSISTANT, $response->role);
            $this->assertSame($contents[$i], $response->content);
        }
    }
}
