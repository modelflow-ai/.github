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
use ModelflowAi\Chat\Request\Message\AIChatMessage;
use ModelflowAi\Chat\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\DecisionTree\Criteria\CriteriaCollection;
use ModelflowAi\OpenaiAdapter\Chat\OpenaiChatAdapter;
use ModelflowAi\OpenaiAdapter\Chat\OpenaiChatAdapterFactory;
use OpenAI\Contracts\ClientContract;
use OpenAI\Contracts\Resources\ChatContract;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Responses\Meta\MetaInformation;
use OpenAI\Testing\Responses\Fixtures\Chat\CreateResponseFixture;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class OpenaiChatAdapterFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testCreateChatAdapter(): void
    {
        $client = $this->prophesize(ClientContract::class);

        $factory = new OpenaiChatAdapterFactory($client->reveal());

        $adapter = $factory->createChatAdapter([
            'model' => 'gpt-4',
            'image_to_text' => true,
            'functions' => true,
            'priority' => 0,
        ]);
        $this->assertInstanceOf(OpenaiChatAdapter::class, $adapter);
    }

    /**
     * @return \Generator<string, array{0: string, 1: string}>
     */
    public static function modelProvider(): \Generator
    {
        yield 'legacy key without dash' => ['gpt4o', 'gpt-4o'];
        yield 'legacy key with version' => ['gpt5.5', 'gpt-5.5'];
        yield 'model id already dashed' => ['gpt-5.6-sol', 'gpt-5.6-sol'];
        yield 'non gpt model' => ['o3-mini', 'o3-mini'];
    }

    #[DataProvider('modelProvider')]
    public function testCreateChatAdapterNormalizesTheModelId(string $configured, string $expected): void
    {
        $chat = $this->prophesize(ChatContract::class);
        $client = $this->prophesize(ClientContract::class);
        $client->chat()->willReturn($chat->reveal());

        $attributes = CreateResponseFixture::ATTRIBUTES;
        $attributes['system_fingerprint'] = '123-123-123';

        $chat->create(Argument::withEntry('model', $expected))
            ->willReturn(CreateResponse::from(
                $attributes,
                MetaInformation::from([
                    'x-request-id' => ['123'],
                    'openai-model' => [$expected],
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
            ))
            ->shouldBeCalled();

        $adapter = (new OpenaiChatAdapterFactory($client->reveal()))->createChatAdapter([
            'model' => $configured,
        ]);

        $adapter->handleRequest(new AIChatRequest(
            new AIChatMessageCollection(
                new AIChatMessage(AIChatMessageRoleEnum::USER, 'User message'),
            ),
            new CriteriaCollection(),
            [],
            [],
            [],
            static fn () => null,
        ));
    }
}
