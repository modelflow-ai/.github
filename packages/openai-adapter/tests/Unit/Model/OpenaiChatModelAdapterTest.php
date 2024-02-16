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

use ModelflowAi\Core\Request\AIChatMessageCollection;
use ModelflowAi\Core\Request\AIChatRequest;
use ModelflowAi\Core\Request\Criteria\AIRequestCriteriaCollection;
use ModelflowAi\Core\Request\Message\AIChatMessage;
use ModelflowAi\Core\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Core\Response\AIChatResponse;
use ModelflowAi\OpenaiAdapter\Model\OpenaiChatModelAdapter;
use OpenAI\Contracts\ClientContract;
use OpenAI\Contracts\Resources\ChatContract;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Responses\Meta\MetaInformation;
use OpenAI\Testing\Responses\Fixtures\Chat\CreateResponseFixture;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class OpenaiChatModelAdapterTest extends TestCase
{
    use ProphecyTrait;

    public function testHandleRequest(): void
    {
        $chat = $this->prophesize(ChatContract::class);
        $client = $this->prophesize(ClientContract::class);
        $client->chat()->willReturn($chat->reveal());

        $chat->create([
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'system', 'content' => 'System message'],
                ['role' => 'user', 'content' => 'User message'],
                ['role' => 'assistant', 'content' => 'Assistant message'],
            ],
        ])->willReturn(CreateResponse::from(
            CreateResponseFixture::ATTRIBUTES,
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
        ), new AIRequestCriteriaCollection(), [], fn () => null);

        $adapter = new OpenaiChatModelAdapter($client->reveal());
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

        $chat->create([
            'model' => 'gpt-4',
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                ['role' => 'system', 'content' => 'System message'],
                ['role' => 'user', 'content' => 'User message'],
                ['role' => 'assistant', 'content' => 'Assistant message'],
            ],
        ])->willReturn(CreateResponse::from(
            CreateResponseFixture::ATTRIBUTES,
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
        ), new AIRequestCriteriaCollection(), ['format' => 'json'], fn () => null);

        $adapter = new OpenaiChatModelAdapter($client->reveal());
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponse::class, $result);
    }
}
