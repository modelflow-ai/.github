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

use ModelflowAi\Core\Request\AIChatMessageCollection;
use ModelflowAi\Core\Request\AIChatRequest;
use ModelflowAi\Core\Request\Criteria\AIRequestCriteriaCollection;
use ModelflowAi\Core\Response\AIChatResponse;
use ModelflowAi\Mistral\ClientInterface;
use ModelflowAi\Mistral\Model;
use ModelflowAi\Mistral\Resources\ChatInterface;
use ModelflowAi\Mistral\Responses\Chat\CreateResponse;
use ModelflowAi\Mistral\Responses\MetaInformation;
use ModelflowAi\MistralAdapter\Model\MistralChatModelAdapter;
use ModelflowAi\PromptTemplate\Chat\AIChatMessage;
use ModelflowAi\PromptTemplate\Chat\AIChatMessageRoleEnum;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class MistralChatModelAdapterTest extends TestCase
{
    use ProphecyTrait;

    public function testEmbedText(): void
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
        ), new AIRequestCriteriaCollection(), fn () => null);

        $adapter = new MistralChatModelAdapter($client->reveal());
        $result = $adapter->handleRequest($request);

        $this->assertInstanceOf(AIChatResponse::class, $result);
        $this->assertSame(AIChatMessageRoleEnum::ASSISTANT, $result->getMessage()->role);
        $this->assertSame('Lorem Ipsum', $result->getMessage()->content);
    }
}
