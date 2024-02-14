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

namespace ModelflowAi\Experts;

use ModelflowAi\Core\AIRequestHandlerInterface;
use ModelflowAi\Core\Request\AIChatRequest;
use ModelflowAi\Core\Request\Builder\AIChatRequestBuilder;
use ModelflowAi\Core\Request\Criteria\CapabilityCriteria;
use ModelflowAi\Core\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Core\Response\AIChatResponse;
use ModelflowAi\Core\Response\AIChatResponseMessage;
use ModelflowAi\Experts\ResponseFormat\JsonSchemaResponseFormat;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ThreadTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<AIRequestHandlerInterface>
     */
    private ObjectProphecy $requestHandler;

    protected function setUp(): void
    {
        $this->requestHandler = $this->prophesize(AIRequestHandlerInterface::class);
    }

    public function testRun(): void
    {
        $expert = new Expert(
            'name',
            'description',
            'instructions',
            [CapabilityCriteria::SMART],
        );

        $thread = new Thread($this->requestHandler->reveal(), $expert);

        $this->requestHandler->createChatRequest()
            ->willReturn(new AIChatRequestBuilder(fn (AIChatRequest $request) => new AIChatResponse(
                $request,
                new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Test message'),
            )));

        $result = $thread->run();
        $this->assertInstanceOf(AIChatResponse::class, $result);

        $request = $result->getRequest();
        $this->assertInstanceOf(AIChatRequest::class, $request);
        $this->assertCount(1, $request->getMessages());
        $this->assertSame(['role' => 'system', 'content' => 'instructions'], $request->getMessages()[0]?->toArray());
    }

    public function testRunWithContext(): void
    {
        $expert = new Expert(
            'name',
            'description',
            'instructions',
            [CapabilityCriteria::SMART],
        );

        $thread = new Thread($this->requestHandler->reveal(), $expert);
        $thread->addContext('key', 'value');

        $this->requestHandler->createChatRequest()
            ->willReturn(new AIChatRequestBuilder(fn (AIChatRequest $request) => new AIChatResponse(
                $request,
                new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Test message'),
            )));

        $result = $thread->run();
        $this->assertInstanceOf(AIChatResponse::class, $result);

        $request = $result->getRequest();
        $this->assertInstanceOf(AIChatRequest::class, $request);
        $this->assertCount(2, $request->getMessages());
        $this->assertSame(['role' => 'system', 'content' => 'instructions'], $request->getMessages()[0]?->toArray());
        $this->assertSame(['role' => 'user', 'content' => 'Context: {"key":"value"}'], $request->getMessages()[1]?->toArray());
    }

    public function testRunWithFormat(): void
    {
        $expert = new Expert(
            'name',
            'description',
            'instructions',
            [CapabilityCriteria::SMART],
            new JsonSchemaResponseFormat([
                'type' => 'object',
                'properties' => [
                    'title' => [
                        'type' => 'string',
                        'description' => 'The title of the webpage, important for SEO and the browser tab',
                        'required' => true,
                    ],
                    'description' => [
                        'type' => 'string',
                        'description' => 'A brief description of the webpage content, important for search engine listings',
                        'required' => true,
                    ],
                    'keywords' => [
                        'type' => 'array',
                        'description' => 'A list of keywords relevant to the webpage content',
                        'items' => [
                            'type' => 'string',
                        ],
                    ],
                ],
                'required' => ['title', 'description'],
            ]),
        );

        $thread = new Thread($this->requestHandler->reveal(), $expert);
        $thread->addContext('key', 'value');

        $this->requestHandler->createChatRequest()
            ->willReturn(new AIChatRequestBuilder(fn (AIChatRequest $request) => new AIChatResponse(
                $request,
                new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Test message'),
            )));

        $result = $thread->run();
        $this->assertInstanceOf(AIChatResponse::class, $result);

        $request = $result->getRequest();
        $this->assertInstanceOf(AIChatRequest::class, $request);
        $this->assertCount(3, $request->getMessages());
        $this->assertSame(['role' => 'system', 'content' => 'instructions'], $request->getMessages()[0]?->toArray());
        $this->assertSame(['role' => 'system', 'content' => <<<Format
Produce a JSON object that includes:
Properties:
- title (Type: string): The title of the webpage, important for SEO and the browser tab
- description (Type: string): A brief description of the webpage content, important for search engine listings
- keywords (Type: array): A list of keywords relevant to the webpage content
Required properties: title, description
It's crucial that your output is a clean JSON object, presented without any additional formatting, annotations, or explanatory content. The response should be ready to use as-is for a system to store it in the database or to process it further.
Format,
        ], $request->getMessages()[1]?->toArray());
        $this->assertSame(['role' => 'user', 'content' => 'Context: {"key":"value"}'], $request->getMessages()[2]?->toArray());
    }
}
