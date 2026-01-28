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
use ModelflowAi\Chat\Request\ResponseFormat\JsonResponseFormat;
use ModelflowAi\Chat\Response\AIChatResponse;
use ModelflowAi\Chat\Response\AIChatResponseStream;
use ModelflowAi\Chat\ToolInfo\ToolInfoBuilder;
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

        $this->assertFalse($adapter->supports($request));
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
