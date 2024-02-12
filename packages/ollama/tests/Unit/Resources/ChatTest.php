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

namespace ModelflowAi\Ollama\Tests\Unit\Resources;

use ModelflowAi\ApiClient\Responses\MetaInformation;
use ModelflowAi\ApiClient\Transport\Enums\ContentType;
use ModelflowAi\ApiClient\Transport\Enums\Method;
use ModelflowAi\ApiClient\Transport\Payload;
use ModelflowAi\ApiClient\Transport\Response\ObjectResponse;
use ModelflowAi\ApiClient\Transport\TransportInterface;
use ModelflowAi\Ollama\Resources\Chat;
use ModelflowAi\Ollama\Resources\ChatInterface;
use ModelflowAi\Ollama\Responses\Chat\CreateResponse;
use ModelflowAi\Ollama\Tests\DataFixtures;
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
            Argument::that(fn (Payload $payload) => 'chat' === $payload->resourceUri->uri
            && Method::POST === $payload->method
            && ContentType::JSON === $payload->contentType
            && @\array_merge(DataFixtures::CHAT_CREATE_REQUEST, ['stream' => false]) === $payload->parameters),
        )->willReturn($response);

        $chat = $this->createInstance($this->transport->reveal());

        $result = $chat->create(DataFixtures::CHAT_CREATE_REQUEST);

        $this->assertInstanceOf(CreateResponse::class, $result);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['model'], $result->model);
        $this->assertSame(
            (new \DateTimeImmutable(DataFixtures::CHAT_CREATE_RESPONSE['created_at']))->getTimestamp(),
            $result->createdAt,
        );
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['message']['role'], $result->message->role);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['message']['content'], $result->message->content);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['done'], $result->done);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['total_duration'], $result->totalDuration);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['load_duration'], $result->loadDuration);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['prompt_eval_duration'], $result->promptEvalDuration);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['prompt_eval_count'], $result->usage->promptTokens);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['eval_count'], $result->usage->totalTokens);
        $this->assertInstanceOf(MetaInformation::class, $result->meta);
    }

    public function testCreateWithImages(): void
    {
        $response = new ObjectResponse(DataFixtures::CHAT_CREATE_RESPONSE, MetaInformation::from([]));
        $this->transport->requestObject(
            Argument::that(fn (Payload $payload) => 'chat' === $payload->resourceUri->uri
            && Method::POST === $payload->method
            && ContentType::JSON === $payload->contentType
            && @\array_merge(DataFixtures::CHAT_CREATE_WITH_IMAGE_REQUEST, ['stream' => false]) === $payload->parameters),
        )->willReturn($response);

        $chat = $this->createInstance($this->transport->reveal());

        $result = $chat->create(DataFixtures::CHAT_CREATE_WITH_IMAGE_REQUEST);

        $this->assertInstanceOf(CreateResponse::class, $result);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['model'], $result->model);
        $this->assertSame(
            (new \DateTimeImmutable(DataFixtures::CHAT_CREATE_RESPONSE['created_at']))->getTimestamp(),
            $result->createdAt,
        );
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['message']['role'], $result->message->role);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['message']['content'], $result->message->content);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['done'], $result->done);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['total_duration'], $result->totalDuration);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['load_duration'], $result->loadDuration);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['prompt_eval_duration'], $result->promptEvalDuration);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['prompt_eval_count'], $result->usage->promptTokens);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['eval_count'], $result->usage->totalTokens);
        $this->assertInstanceOf(MetaInformation::class, $result->meta);
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
