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

use ModelflowAi\Mistral\Resources\Chat;
use ModelflowAi\Mistral\Resources\ChatInterface;
use ModelflowAi\Mistral\Responses\Chat\CreateResponse;
use ModelflowAi\Mistral\Responses\MetaInformation;
use ModelflowAi\Mistral\Tests\DataFixtures;
use ModelflowAi\Mistral\Transport\Enums\ContentType;
use ModelflowAi\Mistral\Transport\Enums\Method;
use ModelflowAi\Mistral\Transport\Payload;
use ModelflowAi\Mistral\Transport\Response\ObjectResponse;
use ModelflowAi\Mistral\Transport\TransportInterface;
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

    private function createInstance(TransportInterface $transport): ChatInterface
    {
        return new Chat($transport);
    }
}
