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

namespace ModelflowAi\Mistral\Tests\Unit\Resources;

use ModelflowAi\ApiClient\Responses\MetaInformation;
use ModelflowAi\ApiClient\Transport\Enums\ContentType;
use ModelflowAi\ApiClient\Transport\Enums\Method;
use ModelflowAi\ApiClient\Transport\Payload;
use ModelflowAi\ApiClient\Transport\Response\ObjectResponse;
use ModelflowAi\ApiClient\Transport\TransportInterface;
use ModelflowAi\Mistral\Model;
use ModelflowAi\Mistral\Resources\Chat;
use ModelflowAi\Mistral\Resources\ChatInterface;
use ModelflowAi\Mistral\Responses\Chat\CreateResponse;
use ModelflowAi\Mistral\Responses\Chat\CreateStreamedResponse;
use ModelflowAi\Mistral\Tests\DataFixtures;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class ChatTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<TransportInterface>
     */
    private ObjectProphecy $transport;

    protected function setUp(): void
    {
        $this->transport = $this->prophesize(TransportInterface::class);
    }

    public function testCreate(): void
    {
        $response = new ObjectResponse(DataFixtures::CHAT_CREATE_RESPONSE, MetaInformation::from([]));
        $this->transport->requestObject(
            Argument::that(fn (Payload $payload) => 'chat/completions' === $payload->resourceUri->uri
            && Method::POST === $payload->method
            && ContentType::JSON === $payload->contentType
            && DataFixtures::CHAT_CREATE_REQUEST === $payload->parameters),
        )->willReturn($response);

        $chat = $this->createInstance($this->transport->reveal());

        $result = $chat->create(DataFixtures::CHAT_CREATE_REQUEST);

        $this->assertInstanceOf(CreateResponse::class, $result);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['id'], $result->id);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['object'], $result->object);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['created'], $result->created);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['model'], $result->model);
        $this->assertCount(\count(DataFixtures::CHAT_CREATE_RESPONSE['choices']), $result->choices);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['choices'][0]['message']['role'], $result->choices[0]->message->role);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['choices'][0]['message']['content'], $result->choices[0]->message->content);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['choices'][0]['finish_reason'], $result->choices[0]->finishReason);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['choices'][1]['message']['role'], $result->choices[1]->message->role);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['choices'][1]['message']['content'], $result->choices[1]->message->content);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['choices'][1]['finish_reason'], $result->choices[1]->finishReason);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['usage']['prompt_tokens'], $result->usage->promptTokens);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['usage']['completion_tokens'], $result->usage->completionTokens);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['usage']['total_tokens'], $result->usage->totalTokens);
    }

    public function testCreateWithFormatForLargeModel(): void
    {
        $response = DataFixtures::CHAT_CREATE_RESPONSE;
        $response['model'] = Model::LARGE->value;
        $response['messages'][0]['content'] = '{"message": "Lorem Ipsum"}';

        $parameters = DataFixtures::CHAT_CREATE_REQUEST;
        $parameters['model'] = Model::LARGE->value;
        $parameters['response_format'] = ['type' => 'json_object'];

        $response = new ObjectResponse($response, MetaInformation::from([]));
        $this->transport->requestObject(
            Argument::that(fn (Payload $payload) => 'chat/completions' === $payload->resourceUri->uri
            && Method::POST === $payload->method
            && ContentType::JSON === $payload->contentType
            && $parameters === $payload->parameters),
        )->willReturn($response);

        $chat = $this->createInstance($this->transport->reveal());

        $result = $chat->create($parameters);

        $this->assertInstanceOf(CreateResponse::class, $result);
    }

    public function testCreateWithFormatForNonLargeModel(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $parameters = DataFixtures::CHAT_CREATE_REQUEST;
        $parameters['model'] = Model::MEDIUM->value;
        $parameters['response_format'] = ['type' => 'json_object'];

        $this->transport->requestObject(Argument::cetera())->shouldNotBeCalled();

        $chat = $this->createInstance($this->transport->reveal());

        $chat->create($parameters);
    }

    public function testCreateWithTools(): void
    {
        $requestData = DataFixtures::CHAT_CREATE_REQUEST_WITH_TOOLS;
        $responseData = DataFixtures::CHAT_CREATE_RESPONSE_WITH_TOOLS;

        $response = new ObjectResponse($responseData, MetaInformation::from([]));
        $this->transport->requestObject(
            Argument::that(fn (Payload $payload) => 'chat/completions' === $payload->resourceUri->uri
                && Method::POST === $payload->method
                && ContentType::JSON === $payload->contentType
                && $requestData === $payload->parameters),
        )->willReturn($response);

        $chat = $this->createInstance($this->transport->reveal());

        $result = $chat->create($requestData);

        $this->assertInstanceOf(CreateResponse::class, $result);
        $this->assertSame($responseData['id'], $result->id);
        $this->assertSame($responseData['object'], $result->object);
        $this->assertSame($responseData['created'], $result->created);
        $this->assertSame($responseData['model'], $result->model);
        $this->assertCount(\count($responseData['choices']), $result->choices);
        $this->assertSame($responseData['choices'][0]['message']['role'], $result->choices[0]->message->role);
        $this->assertSame($responseData['choices'][0]['message']['content'], $result->choices[0]->message->content);
        $this->assertCount(\count($responseData['choices'][0]['message']['tool_calls']), $result->choices[0]->message->toolCalls);
        $this->assertSame($responseData['choices'][0]['message']['tool_calls'][0]['id'], $result->choices[0]->message->toolCalls[0]->id);
        $this->assertSame($responseData['choices'][0]['message']['tool_calls'][0]['type'], $result->choices[0]->message->toolCalls[0]->type);
        $this->assertSame($responseData['choices'][0]['message']['tool_calls'][0]['function']['name'], $result->choices[0]->message->toolCalls[0]->function->name);
        $this->assertSame($responseData['choices'][0]['message']['tool_calls'][0]['function']['arguments'], $result->choices[0]->message->toolCalls[0]->function->arguments);
        $this->assertSame($responseData['choices'][0]['message']['tool_calls'][1]['id'], $result->choices[0]->message->toolCalls[1]->id);
        $this->assertSame($responseData['choices'][0]['message']['tool_calls'][1]['type'], $result->choices[0]->message->toolCalls[1]->type);
        $this->assertSame($responseData['choices'][0]['message']['tool_calls'][1]['function']['name'], $result->choices[0]->message->toolCalls[1]->function->name);
        $this->assertSame($responseData['choices'][0]['message']['tool_calls'][1]['function']['arguments'], $result->choices[0]->message->toolCalls[1]->function->arguments);
        $this->assertSame($responseData['choices'][0]['finish_reason'], $result->choices[0]->finishReason);
        $this->assertSame($responseData['usage']['prompt_tokens'], $result->usage->promptTokens);
        $this->assertSame($responseData['usage']['completion_tokens'], $result->usage->completionTokens);
        $this->assertSame($responseData['usage']['total_tokens'], $result->usage->totalTokens);
    }

    public function testCreateWithToolsForNonLargeModel(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $requestData = DataFixtures::CHAT_CREATE_REQUEST_WITH_TOOLS;
        $requestData['model'] = Model::MEDIUM->value;

        $this->transport->requestObject(Argument::cetera())->shouldNotBeCalled();

        $chat = $this->createInstance($this->transport->reveal());

        $chat->create($requestData);
    }

    public function testCreateAsStream(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $chat = $this->createInstance($this->transport->reveal());

        $parameters = DataFixtures::CHAT_CREATE_REQUEST;
        $parameters['stream'] = true;

        $chat->create($parameters);
    }

    public function testCreateMissingModel(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $chat = $this->createInstance($this->transport->reveal());

        $parameters = [
            'messages' => DataFixtures::CHAT_CREATE_REQUEST['messages'],
        ];

        // @phpstan-ignore-next-line
        $chat->create($parameters);
    }

    public function testCreateMissingMessages(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $chat = $this->createInstance($this->transport->reveal());

        $parameters = [
            'model' => DataFixtures::CHAT_CREATE_REQUEST['model'],
        ];

        // @phpstan-ignore-next-line
        $chat->create($parameters);
    }

    public function testCreateStreamed(): void
    {
        $responses = [];
        foreach (DataFixtures::CHAT_CREATE_STREAMED_RESPONSES as $response) {
            $responses[] = new ObjectResponse($response, MetaInformation::from([]));
        }

        $this->transport->requestStream(
            Argument::that(fn (Payload $payload) => 'chat/completions' === $payload->resourceUri->uri
                && Method::POST === $payload->method
                && ContentType::JSON === $payload->contentType
                && @\array_merge(DataFixtures::CHAT_CREATE_REQUEST, ['stream' => true]) === $payload->parameters),
            Argument::any(),
        )->willReturn(new \ArrayIterator($responses));

        $chat = $this->createInstance($this->transport->reveal());

        $result = \iterator_to_array($chat->createStreamed(DataFixtures::CHAT_CREATE_REQUEST));

        $this->assertCount(2, $result);

        foreach ($result as $i => $response) {
            $this->assertInstanceOf(CreateStreamedResponse::class, $response);
            $this->assertSame(DataFixtures::CHAT_CREATE_STREAMED_RESPONSES[$i]['id'], $response->id);
            $this->assertSame(DataFixtures::CHAT_CREATE_STREAMED_RESPONSES[$i]['object'], $response->object);
            $this->assertSame(DataFixtures::CHAT_CREATE_STREAMED_RESPONSES[$i]['created'], $response->created);
            $this->assertSame(DataFixtures::CHAT_CREATE_STREAMED_RESPONSES[$i]['model'], $response->model);
            $this->assertCount(\count(DataFixtures::CHAT_CREATE_STREAMED_RESPONSES[$i]['choices']), $response->choices);
            $this->assertSame(DataFixtures::CHAT_CREATE_STREAMED_RESPONSES[$i]['choices'][0]['delta']['role'], $response->choices[0]->delta->role);
            $this->assertSame(DataFixtures::CHAT_CREATE_STREAMED_RESPONSES[$i]['choices'][0]['delta']['content'], $response->choices[0]->delta->content);
            $this->assertSame(DataFixtures::CHAT_CREATE_STREAMED_RESPONSES[$i]['choices'][0]['finish_reason'], $response->choices[0]->finishReason);
        }
    }

    public function testCreateStreamedMissingModel(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $chat = $this->createInstance($this->transport->reveal());

        $parameters = [
            'messages' => DataFixtures::CHAT_CREATE_REQUEST['messages'],
        ];

        // @phpstan-ignore-next-line
        \iterator_to_array($chat->createStreamed($parameters));
    }

    public function testCreateStreamedMissingMessages(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $chat = $this->createInstance($this->transport->reveal());

        $parameters = [
            'model' => DataFixtures::CHAT_CREATE_REQUEST['model'],
        ];

        // @phpstan-ignore-next-line
        \iterator_to_array($chat->createStreamed($parameters));
    }

    public function testCreateStreamedWithTools(): void
    {
        $requestData = DataFixtures::CHAT_CREATE_REQUEST_WITH_TOOLS;
        $responseData = DataFixtures::CHAT_CREATE_STREAMED_RESPONSES_WITH_TOOLS;

        $responses = [];
        foreach ($responseData as $response) {
            $responses[] = new ObjectResponse($response, MetaInformation::from([]));
        }

        $this->transport->requestStream(
            Argument::that(fn (Payload $payload) => 'chat/completions' === $payload->resourceUri->uri
                && Method::POST === $payload->method
                && ContentType::JSON === $payload->contentType
                && @\array_merge($requestData, ['stream' => true]) === $payload->parameters),
            Argument::any(),
        )->willReturn(new \ArrayIterator($responses));

        $chat = $this->createInstance($this->transport->reveal());

        $result = \iterator_to_array($chat->createStreamed($requestData));

        $this->assertCount(2, $result);

        foreach ($result as $i => $response) {
            $this->assertInstanceOf(CreateStreamedResponse::class, $response);
            $this->assertSame($responseData[$i]['id'], $response->id);
            $this->assertSame($responseData[$i]['object'], $response->object);
            $this->assertSame($responseData[$i]['created'], $response->created);
            $this->assertSame($responseData[$i]['model'], $response->model);
            $this->assertCount(\count($responseData[$i]['choices']), $response->choices);
            $this->assertSame($responseData[$i]['choices'][0]['delta']['role'], $response->choices[0]->delta->role);
            $this->assertSame($responseData[$i]['choices'][0]['delta']['content'], $response->choices[0]->delta->content);
            $this->assertCount(\count($responseData[$i]['choices'][0]['delta']['tool_calls']), $response->choices[0]->delta->toolCalls);
            $this->assertSame($responseData[$i]['choices'][0]['delta']['tool_calls'][0]['function']['name'], $response->choices[0]->delta->toolCalls[0]->function->name);
            $this->assertSame($responseData[$i]['choices'][0]['delta']['tool_calls'][0]['function']['arguments'], $response->choices[0]->delta->toolCalls[0]->function->arguments);
            $this->assertSame($responseData[$i]['choices'][0]['finish_reason'], $response->choices[0]->finishReason);
        }
    }

    public function testCreateStreamedWithToolsForNoneLarge(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $requestData = DataFixtures::CHAT_CREATE_REQUEST_WITH_TOOLS;
        $requestData['model'] = Model::MEDIUM->value;

        $chat = $this->createInstance($this->transport->reveal());

        \iterator_to_array($chat->createStreamed($requestData));
    }

    private function createInstance(TransportInterface $transport): ChatInterface
    {
        return new Chat($transport);
    }
}
