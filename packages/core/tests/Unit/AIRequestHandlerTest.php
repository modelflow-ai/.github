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

namespace ModelflowAi\Core\Tests\Unit;

use ModelflowAi\Core\AIRequestHandler;
use ModelflowAi\Core\DecisionTree\AIModelDecisionTreeInterface;
use ModelflowAi\Core\Model\AIModelAdapterInterface;
use ModelflowAi\Core\Request\AIChatRequest;
use ModelflowAi\Core\Request\AICompletionRequest;
use ModelflowAi\Core\Request\Builder\AIChatRequestBuilder;
use ModelflowAi\Core\Request\Builder\AICompletionRequestBuilder;
use ModelflowAi\Core\Request\Message\AIChatMessage;
use ModelflowAi\Core\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Core\Response\AIChatResponse;
use ModelflowAi\Core\Response\AIChatResponseMessage;
use ModelflowAi\Core\Response\AICompletionResponse;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class AIRequestHandlerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<AIModelAdapterInterface>
     */
    private ObjectProphecy $adapter;

    /**
     * @var ObjectProphecy<AIModelDecisionTreeInterface>
     */
    private ObjectProphecy $decisionTree;

    private AIRequestHandler $aiRequestHandler;

    protected function setUp(): void
    {
        $this->adapter = $this->prophesize(AIModelAdapterInterface::class);
        $this->decisionTree = $this->prophesize(AIModelDecisionTreeInterface::class);
        $this->aiRequestHandler = new AIRequestHandler($this->decisionTree->reveal());
    }

    public function testCreateCompletionRequest(): void
    {
        $textRequest = $this->aiRequestHandler->createCompletionRequest('Test content');

        $this->assertInstanceOf(AICompletionRequestBuilder::class, $textRequest);
    }

    public function testCreateChatRequest(): void
    {
        $chatRequest = $this->aiRequestHandler->createChatRequest();

        $this->assertInstanceOf(AIChatRequestBuilder::class, $chatRequest);
    }

    public function testHandleCompletionRequest(): void
    {
        $textRequest = $this->aiRequestHandler->createCompletionRequest('Test content')->build();

        $response = new AICompletionResponse($textRequest, 'Response content');
        $this->adapter->handleRequest(Argument::type(AICompletionRequest::class))->willReturn($response);
        $this->decisionTree->determineAdapter($textRequest)->willReturn($this->adapter->reveal());

        $result = $textRequest->execute();

        $this->assertSame($response, $result);
    }

    public function testHandleChatRequest(): void
    {
        $chatRequest = $this->aiRequestHandler->createChatRequest(
            new AIChatMessage(AIChatMessageRoleEnum::USER, 'Test content'),
        )->build();

        $response = new AIChatResponse(
            $chatRequest,
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Response content'),
        );
        $this->adapter->handleRequest(Argument::type(AIChatRequest::class))->willReturn($response);
        $this->decisionTree->determineAdapter($chatRequest)->willReturn($this->adapter->reveal());

        $result = $chatRequest->execute();

        $this->assertSame($response, $result);
    }
}
