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

namespace ModelflowAi\Chat\Tests\Unit;

use ModelflowAi\Chat\Adapter\AIChatAdapterInterface;
use ModelflowAi\Chat\AIChatRequestHandler;
use ModelflowAi\Chat\Request\AIChatMessageCollection;
use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\Chat\Request\AIChatStreamedRequest;
use ModelflowAi\Chat\Request\Builder\AIChatRequestBuilder;
use ModelflowAi\Chat\Request\Message\AIChatMessage;
use ModelflowAi\Chat\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Chat\Request\ResponseFormat\JsonSchemaResponseFormat;
use ModelflowAi\Chat\Request\ResponseFormat\ResponseFormatInterface;
use ModelflowAi\Chat\Request\ResponseFormat\SupportsResponseFormatInterface;
use ModelflowAi\Chat\Response\AIChatResponse;
use ModelflowAi\Chat\Response\AIChatResponseInterface;
use ModelflowAi\Chat\Response\AIChatResponseMessage;
use ModelflowAi\Chat\Response\AIChatResponseStream;
use ModelflowAi\Chat\Response\Usage;
use ModelflowAi\DecisionTree\DecisionTreeInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class AIChatRequestHandlerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<AIChatAdapterInterface>
     */
    private ObjectProphecy $adapter;

    /**
     * @var ObjectProphecy<DecisionTreeInterface<AIChatRequest, AIChatAdapterInterface>>
     */
    private ObjectProphecy $decisionTree;

    private AIChatRequestHandler $aiRequestHandler;

    protected function setUp(): void
    {
        $this->adapter = $this->prophesize(AIChatAdapterInterface::class);
        /** @var ObjectProphecy<DecisionTreeInterface<AIChatRequest, AIChatAdapterInterface>> $decisionTree */
        $decisionTree = $this->prophesize(DecisionTreeInterface::class);
        $this->decisionTree = $decisionTree;
        $this->aiRequestHandler = new AIChatRequestHandler($this->decisionTree->reveal());
    }

    public function testCreateRequest(): void
    {
        $chatRequest = $this->aiRequestHandler->createRequest();

        $this->assertInstanceOf(AIChatRequestBuilder::class, $chatRequest);
    }

    public function testHandleChatRequest(): void
    {
        $chatRequest = $this->aiRequestHandler->createRequest(
            new AIChatMessage(AIChatMessageRoleEnum::USER, 'Test content'),
        )->build();

        $response = new AIChatResponse(
            $chatRequest,
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Response content'),
            new Usage(0, 0, 0),
        );
        $this->adapter->handleRequest(Argument::type(AIChatRequest::class))->willReturn($response);
        $this->decisionTree->determineAdapter($chatRequest)->willReturn($this->adapter->reveal());

        $result = $chatRequest->execute();

        $this->assertSame($response, $result);
    }

    public function testHandleChatStreamedRequest(): void
    {
        $chatRequest = $this->aiRequestHandler->createStreamedRequest(
            new AIChatMessage(AIChatMessageRoleEnum::USER, 'Test content'),
        )->build();

        $response = new AIChatResponseStream(
            $chatRequest,
            new \ArrayIterator([]),
        );
        $this->adapter->handleRequest(Argument::type(AIChatStreamedRequest::class))->willReturn($response);
        $this->decisionTree->determineAdapter($chatRequest)->willReturn($this->adapter->reveal());

        $result = $chatRequest->execute();

        $this->assertSame($response, $result);
    }

    public function testHandleWithoutResponseFormat(): void
    {
        // Arrange
        $mockDecisionTree = $this->createMock(DecisionTreeInterface::class);
        $mockAdapter = $this->createMock(AIChatAdapterInterface::class);

        $mockDecisionTree
            ->method('determineAdapter')
            ->willReturn($mockAdapter);

        $mockAdapter
            ->method('handleRequest')
            ->willReturnCallback(
                static fn (AIChatRequest $request) => new AIChatResponse(
                    $request,
                    new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'No format'),
                    null,
                ),
            );

        $handler = new AIChatRequestHandler($mockDecisionTree);

        // Create a request builder and build a request without responseFormat
        $requestBuilder = $handler->createRequest(
            new AIChatMessage(AIChatMessageRoleEnum::USER, 'Hello, how are you?'),
        );
        $request = $requestBuilder->build(); // Typically calls the builder's internal closure

        // Act
        $response = $request->execute();

        // Assert
        $this->assertInstanceOf(AIChatResponse::class, $response);
        $this->assertSame('No format', $response->getMessage()->content);
    }

    public function testHandleWithResponseFormatAdapterSupports(): void
    {
        // Arrange
        $mockDecisionTree = $this->createMock(DecisionTreeInterface::class);
        $mockAdapter = $this->createMock(AIChatAdapterInterface::class);

        // Make our adapter implement the SupportsResponseFormatInterface
        $mockSupportedAdapter = new class($mockAdapter) implements AIChatAdapterInterface, SupportsResponseFormatInterface {
            public function __construct(
                private readonly AIChatAdapterInterface $wrapped,
            ) {
            }

            public function handleRequest(AIChatRequest $request): AIChatResponseInterface
            {
                return $this->wrapped->handleRequest($request);
            }

            public function supportsResponseFormat(ResponseFormatInterface $responseFormat): bool
            {
                // For this scenario, we simulate that the adapter DOES support the format
                return true;
            }

            public function supports(object $request): bool
            {
                return true;
            }
        };

        $mockDecisionTree
            ->method('determineAdapter')
            ->willReturn($mockSupportedAdapter);

        $mockAdapter
            ->method('handleRequest')
            ->willReturnCallback(
                static fn (AIChatRequest $request) => new AIChatResponse(
                    $request,
                    new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Supported'),
                    null,
                ),
            );

        $handler = new AIChatRequestHandler($mockDecisionTree);

        $schema = [
            'name' => 'TestObject',
            'description' => 'A schema description',
            'type' => 'object',
            'properties' => [
                'foo' => [
                    'type' => 'string',
                ],
            ],
            'required' => ['foo'],
        ];

        // Create a request with a response format
        $requestBuilder = $handler->createRequest(
            new AIChatMessage(AIChatMessageRoleEnum::USER, 'Hello, do you support JSON?'),
        )->asJson($schema);
        $request = $requestBuilder->build();

        // Act
        $response = $request->execute();

        // Assert
        $this->assertInstanceOf(AIChatResponse::class, $response);
        $this->assertSame('Supported', $response->getMessage()->content);
    }

    public function testHandleWithResponseFormatAdapterDoesNotSupport(): void
    {
        // Arrange
        $mockDecisionTree = $this->createMock(DecisionTreeInterface::class);
        $mockAdapter = $this->createMock(AIChatAdapterInterface::class);

        // Make our adapter implement the SupportsResponseFormatInterface
        $mockUnsupportedAdapter = new class($mockAdapter) implements AIChatAdapterInterface, SupportsResponseFormatInterface {
            public function __construct(
                private readonly AIChatAdapterInterface $wrapped,
            ) {
            }

            public function handleRequest(AIChatRequest $request): AIChatResponseInterface
            {
                return $this->wrapped->handleRequest($request);
            }

            public function supportsResponseFormat(ResponseFormatInterface $responseFormat): bool
            {
                // For this scenario, we simulate that the adapter DOES NOT support the format
                return false;
            }

            public function supports(object $request): bool
            {
                return true;
            }
        };

        $mockDecisionTree
            ->method('determineAdapter')
            ->willReturn($mockUnsupportedAdapter);

        // Here we’ll track if the adapter is called
        $mockAdapter
            ->method('handleRequest')
            ->willReturnCallback(
                static fn (AIChatRequest $request) => new AIChatResponse(
                    $request,
                    new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Not supported'),
                    null,
                ),
            );

        $handler = new AIChatRequestHandler($mockDecisionTree);

        $schema = [
            'name' => 'TestObject',
            'description' => 'A schema description',
            'type' => 'object',
            'properties' => [
                'foo' => [
                    'type' => 'string',
                ],
            ],
            'required' => ['foo'],
        ];
        $mockResponseFormat = new JsonSchemaResponseFormat($schema);

        // Create a request with a response format
        $requestBuilder = $handler->createRequest(
            new AIChatMessage(AIChatMessageRoleEnum::USER, 'Hello, do you support the new format?'),
        )->asJson($schema);
        $request = $requestBuilder->build();

        // Act
        $response = $request->execute();

        // Assert
        $this->assertInstanceOf(AIChatResponse::class, $response);
        $this->assertSame('Not supported', $response->getMessage()->content);

        // Check that the request messages have a new system message added for the format
        $messagesCollection = $request->getMessages();
        $this->assertInstanceOf(AIChatMessageCollection::class, $messagesCollection);

        $allMessages = $messagesCollection->toArray();
        // Expect at least 2 messages: the original user message + the new "SYSTEM" message for the format
        $this->assertCount(2, $allMessages);

        $this->assertSame(AIChatMessageRoleEnum::SYSTEM->value, $allMessages[0]['role']); // The inserted "SYSTEM" message describing the format
        $this->assertSame(AIChatMessageRoleEnum::USER->value, $allMessages[1]['role']); // Original message
        $this->assertStringContainsString($mockResponseFormat->asString(), $allMessages[0]['content']);
    }

    public function testHandleWithResponseFormatAdapterDoesNotImplementInterface(): void
    {
        // Arrange
        $mockDecisionTree = $this->createMock(DecisionTreeInterface::class);
        $mockAdapter = $this->createMock(AIChatAdapterInterface::class);

        // Make our adapter implement the SupportsResponseFormatInterface
        $mockUnsupportedAdapter = new class($mockAdapter) implements AIChatAdapterInterface {
            public function __construct(
                private readonly AIChatAdapterInterface $wrapped,
            ) {
            }

            public function handleRequest(AIChatRequest $request): AIChatResponseInterface
            {
                return $this->wrapped->handleRequest($request);
            }

            public function supports(object $request): bool
            {
                return true;
            }
        };

        $mockDecisionTree
            ->method('determineAdapter')
            ->willReturn($mockUnsupportedAdapter);

        // Here we’ll track if the adapter is called
        $mockAdapter
            ->method('handleRequest')
            ->willReturnCallback(
                static fn (AIChatRequest $request) => new AIChatResponse(
                    $request,
                    new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Not supported'),
                    null,
                ),
            );

        $handler = new AIChatRequestHandler($mockDecisionTree);

        $schema = [
            'name' => 'TestObject',
            'description' => 'A schema description',
            'type' => 'object',
            'properties' => [
                'foo' => [
                    'type' => 'string',
                ],
            ],
            'required' => ['foo'],
        ];
        $mockResponseFormat = new JsonSchemaResponseFormat($schema);

        // Create a request with a response format
        $requestBuilder = $handler->createRequest(
            new AIChatMessage(AIChatMessageRoleEnum::USER, 'Hello, do you support the new format?'),
        )->asJson($schema);
        $request = $requestBuilder->build();

        // Act
        $response = $request->execute();

        // Assert
        $this->assertInstanceOf(AIChatResponse::class, $response);
        $this->assertSame('Not supported', $response->getMessage()->content);

        // Check that the request messages have a new system message added for the format
        $messagesCollection = $request->getMessages();
        $this->assertInstanceOf(AIChatMessageCollection::class, $messagesCollection);

        $allMessages = $messagesCollection->toArray();
        // Expect at least 2 messages: the original user message + the new "SYSTEM" message for the format
        $this->assertCount(2, $allMessages);

        $this->assertSame(AIChatMessageRoleEnum::SYSTEM->value, $allMessages[0]['role']); // The inserted "SYSTEM" message describing the format
        $this->assertSame(AIChatMessageRoleEnum::USER->value, $allMessages[1]['role']); // Original message
        $this->assertStringContainsString($mockResponseFormat->asString(), $allMessages[0]['content']);
    }
}
