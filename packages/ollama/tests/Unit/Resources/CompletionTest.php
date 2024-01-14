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
use ModelflowAi\Ollama\Resources\Completion;
use ModelflowAi\Ollama\Resources\CompletionInterface;
use ModelflowAi\Ollama\Responses\Completion\CreateResponse;
use ModelflowAi\Ollama\Tests\DataFixtures;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class CompletionTest extends TestCase
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
        $response = new ObjectResponse(DataFixtures::COMPLETION_CREATE_RESPONSE, MetaInformation::from([]));
        $this->transport->requestObject(
            Argument::that(fn (Payload $payload) => 'generate' === $payload->resourceUri->uri
            && Method::POST === $payload->method
            && ContentType::JSON === $payload->contentType
            && @\array_merge(DataFixtures::COMPLETION_CREATE_REQUEST, ['format' => 'json', 'stream' => false]) === $payload->parameters),
        )->willReturn($response);

        $completion = $this->createInstance($this->transport->reveal());

        $result = $completion->create(DataFixtures::COMPLETION_CREATE_REQUEST);

        $this->assertInstanceOf(CreateResponse::class, $result);
        $this->assertSame(DataFixtures::COMPLETION_CREATE_RESPONSE['model'], $result->model);
        $this->assertSame(
            (new \DateTimeImmutable(DataFixtures::COMPLETION_CREATE_RESPONSE['created_at']))->getTimestamp(),
            $result->createdAt,
        );
        $this->assertSame(DataFixtures::COMPLETION_CREATE_RESPONSE['response'], $result->response);
        $this->assertSame(DataFixtures::COMPLETION_CREATE_RESPONSE['done'], $result->done);
        $this->assertSame(DataFixtures::COMPLETION_CREATE_RESPONSE['total_duration'], $result->totalDuration);
        $this->assertSame(DataFixtures::COMPLETION_CREATE_RESPONSE['load_duration'], $result->loadDuration);
        $this->assertSame(DataFixtures::COMPLETION_CREATE_RESPONSE['prompt_eval_duration'], $result->promptEvalDuration);
        $this->assertSame(DataFixtures::COMPLETION_CREATE_RESPONSE['prompt_eval_count'], $result->usage->promptTokens);
        $this->assertSame(DataFixtures::COMPLETION_CREATE_RESPONSE['eval_count'], $result->usage->totalTokens);
        $this->assertInstanceOf(MetaInformation::class, $result->meta);
    }

    public function testCreateAsStream(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $completion = $this->createInstance($this->transport->reveal());

        $parameters = DataFixtures::COMPLETION_CREATE_REQUEST;
        $parameters['stream'] = true;

        $completion->create($parameters);
    }

    public function testCreateMissingModel(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $completion = $this->createInstance($this->transport->reveal());

        $parameters = [
            'prompt' => DataFixtures::COMPLETION_CREATE_REQUEST['prompt'],
        ];

        // @phpstan-ignore-next-line
        $completion->create($parameters);
    }

    public function testCreateMissingPrompt(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $completion = $this->createInstance($this->transport->reveal());

        $parameters = [
            'model' => DataFixtures::COMPLETION_CREATE_REQUEST['model'],
        ];

        // @phpstan-ignore-next-line
        $completion->create($parameters);
    }

    private function createInstance(TransportInterface $transport): CompletionInterface
    {
        return new Completion($transport);
    }
}
