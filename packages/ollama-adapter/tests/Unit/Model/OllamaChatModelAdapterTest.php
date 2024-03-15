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

namespace ModelflowAi\OllamaAdapter\Tests\Unit\Model;

use ModelflowAi\ApiClient\Responses\MetaInformation;
use ModelflowAi\Core\Request\AIChatMessageCollection;
use ModelflowAi\Core\Request\AIChatRequest;
use ModelflowAi\Core\Request\Criteria\AIRequestCriteriaCollection;
use ModelflowAi\Core\Request\Message\AIChatMessage;
use ModelflowAi\Core\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Core\Response\AIChatResponse;
use ModelflowAi\Core\Response\AIChatResponseStream;
use ModelflowAi\Ollama\ClientInterface;
use ModelflowAi\Ollama\Resources\ChatInterface;
use ModelflowAi\Ollama\Responses\Chat\CreateResponse;
use ModelflowAi\Ollama\Responses\Chat\CreateStreamedResponse;
use ModelflowAi\OllamaAdapter\Model\OllamaChatModelAdapter;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class OllamaChatModelAdapterTest extends TestCase
{
    use ProphecyTrait;

    public function testHandleRequest(): void
    {
        $chat = $this->prophesize(ChatInterface::class);
        $client = $this->prophesize(ClientInterface::class);
        $client->chat()->willReturn($chat->reveal());

        $chat->create([
            'model' => 'llama2',
            'messages' => [
                ['role' => 'system', 'content' => 'System message'],
                ['role' => 'user', 'content' => 'User message'],
                ['role' => 'assistant', 'content' => 'Assistant message'],
            ],
        ])->willReturn(CreateResponse::from([
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
        ], MetaInformation::from([])));

        $request = new AIChatRequest(new AIChatMessageCollection(
            new AIChatMessage(AIChatMessageRoleEnum::SYSTEM, 'System message'),
            new AIChatMessage(AIChatMessageRoleEnum::USER, 'User message'),
            new AIChatMessage(AIChatMessageRoleEnum::ASSISTANT, 'Assistant message'),
        ), new AIRequestCriteriaCollection(), [], [], [], fn () => null);

        $adapter = new OllamaChatModelAdapter($client->reveal());
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponse::class, $result);
        $this->assertSame(AIChatMessageRoleEnum::ASSISTANT, $result->getMessage()->role);
        $this->assertSame('Lorem Ipsum', $result->getMessage()->content);
    }

    public function testHandleRequestAsJson(): void
    {
        $chat = $this->prophesize(ChatInterface::class);
        $client = $this->prophesize(ClientInterface::class);
        $client->chat()->willReturn($chat->reveal());

        $chat->create([
            'model' => 'llama2',
            'format' => 'json',
            'messages' => [
                ['role' => 'system', 'content' => 'System message'],
                ['role' => 'user', 'content' => 'User message'],
                ['role' => 'assistant', 'content' => 'Assistant message'],
            ],
        ])->willReturn(CreateResponse::from([
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
        ], MetaInformation::from([])));

        $request = new AIChatRequest(new AIChatMessageCollection(
            new AIChatMessage(AIChatMessageRoleEnum::SYSTEM, 'System message'),
            new AIChatMessage(AIChatMessageRoleEnum::USER, 'User message'),
            new AIChatMessage(AIChatMessageRoleEnum::ASSISTANT, 'Assistant message'),
        ), new AIRequestCriteriaCollection(), [], [], ['format' => 'json'], fn () => null);

        $adapter = new OllamaChatModelAdapter($client->reveal());
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponse::class, $result);
    }

    public function testHandleRequestStreamed(): void
    {
        $chat = $this->prophesize(ChatInterface::class);
        $client = $this->prophesize(ClientInterface::class);
        $client->chat()->willReturn($chat->reveal());

        $chat->createStreamed([
            'model' => 'llama2',
            'format' => 'json',
            'messages' => [
                ['role' => 'system', 'content' => 'System message'],
                ['role' => 'user', 'content' => 'User message'],
                ['role' => 'assistant', 'content' => 'Assistant message'],
            ],
        ])->willReturn(new \ArrayIterator([
            CreateStreamedResponse::from(0, [
                'model' => 'llama2',
                'created_at' => '2024-01-13T12:01:31.929209Z',
                'message' => [
                    'role' => 'assistant',
                    'content' => 'Lorem',
                ],
                'done' => false,
            ], MetaInformation::from([])),
            CreateStreamedResponse::from(1, [
                'model' => 'llama2',
                'created_at' => '2024-01-13T12:01:31.929209Z',
                'message' => [
                    'role' => 'assistant',
                    'content' => 'Ipsum',
                ],
                'done' => true,
            ], MetaInformation::from([])),
        ], ));

        $request = new AIChatRequest(new AIChatMessageCollection(
            new AIChatMessage(AIChatMessageRoleEnum::SYSTEM, 'System message'),
            new AIChatMessage(AIChatMessageRoleEnum::USER, 'User message'),
            new AIChatMessage(AIChatMessageRoleEnum::ASSISTANT, 'Assistant message'),
        ), new AIRequestCriteriaCollection(), [], [], ['format' => 'json', 'streamed' => true], fn () => null);

        $adapter = new OllamaChatModelAdapter($client->reveal());
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponseStream::class, $result);
        $contents = ['Lorem', 'Ipsum'];
        foreach ($result->getMessageStream() as $i => $response) {
            $this->assertSame(AIChatMessageRoleEnum::ASSISTANT, $response->role);
            $this->assertSame($contents[$i], $response->content);
        }
    }
}
