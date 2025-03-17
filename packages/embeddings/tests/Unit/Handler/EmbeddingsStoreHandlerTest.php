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

namespace ModelflowAi\Embeddings\Tests\Unit\Handler;

use ModelflowAi\Embeddings\Adapter\EmbeddingAdapterInterface;
use ModelflowAi\Embeddings\Adapter\Request\EmbedRequest;
use ModelflowAi\Embeddings\Adapter\Response\EmbedResponse;
use ModelflowAi\Embeddings\Generator\EmbeddingGeneratorInterface;
use ModelflowAi\Embeddings\Handler\EmbeddingsStoreHandler;
use ModelflowAi\Embeddings\Model\EmbeddingInterface;
use ModelflowAi\Embeddings\Request\EmbeddingsStoreRequest;
use ModelflowAi\Embeddings\Store\EmbeddingsStoreInterface;
use ModelflowAi\Embeddings\Usage\EmbeddingUsage;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class EmbeddingsStoreHandlerTest extends TestCase
{
    use ProphecyTrait;

    public function testHandle(): void
    {
        $key = 'test_key';
        $vector = [0.1, 0.2, 0.3];

        $embedding = new TestEmbedding('test-id', 'test content');
        $generatedEmbedding = new TestEmbedding('test-id', 'processed content');

        $generator = $this->prophesize(EmbeddingGeneratorInterface::class);
        $store = $this->prophesize(EmbeddingsStoreInterface::class);
        $adapter = $this->prophesize(EmbeddingAdapterInterface::class);

        $generator->generateEmbedding($embedding, null)->willReturn([$generatedEmbedding]);

        $embedResponse = new EmbedResponse($vector, new EmbeddingUsage(10, 20));
        $adapter->embed(Argument::that(fn (EmbedRequest $request) => 'processed content' === $request->getText()))->willReturn($embedResponse);

        $store->addDocuments([$generatedEmbedding])->shouldBeCalled();

        $handler = new EmbeddingsStoreHandler(
            [$key => $generator->reveal()],
            [$key => $store->reveal()],
            [$key => $adapter->reveal()],
            [TestEmbedding::class => $key],
        );

        $request = new EmbeddingsStoreRequest(
            function () {},
            [$embedding],
        );

        $response = $handler->handle($request);

        $this->assertCount(1, $response->getEmbeddings());
        $this->assertSame($generatedEmbedding, $response->getEmbeddings()[0]);
        $this->assertSame($vector, $generatedEmbedding->getVector());
        $this->assertSame(10, $response->getUsage()->getPromptTokens());
        $this->assertSame(20, $response->getUsage()->getTotalTokens());
    }

    public function testHandleWithHeaderGenerator(): void
    {
        $key = 'test_key';
        $vector = [0.1, 0.2, 0.3];
        $headerGenerator = fn () => ['header' => 'value'];

        $embedding = new TestEmbedding('test-id', 'test content');
        $generatedEmbedding = new TestEmbedding('test-id', 'processed content');

        $generator = $this->prophesize(EmbeddingGeneratorInterface::class);
        $store = $this->prophesize(EmbeddingsStoreInterface::class);
        $adapter = $this->prophesize(EmbeddingAdapterInterface::class);

        $generator->generateEmbedding($embedding, $headerGenerator)->willReturn([$generatedEmbedding]);

        $embedResponse = new EmbedResponse($vector, new EmbeddingUsage(10, 20));
        $adapter->embed(Argument::any())->willReturn($embedResponse);

        $store->addDocuments([$generatedEmbedding])->shouldBeCalled();

        $handler = new EmbeddingsStoreHandler(
            [$key => $generator->reveal()],
            [$key => $store->reveal()],
            [$key => $adapter->reveal()],
            [TestEmbedding::class => $key],
        );

        $request = new EmbeddingsStoreRequest(
            function () {},
            [$embedding],
            $headerGenerator,
        );

        $response = $handler->handle($request);

        $this->assertCount(1, $response->getEmbeddings());
        $this->assertSame($generatedEmbedding, $response->getEmbeddings()[0]);
    }

    public function testHandleWithInvalidEmbeddingClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No mapping configured for embedding class "ModelflowAi\Embeddings\Tests\Unit\Handler\TestEmbedding".');

        $handler = new EmbeddingsStoreHandler([], [], [], []);
        $request = new EmbeddingsStoreRequest(
            function () {},
            [new TestEmbedding('test-id', 'test content')],
        );

        $handler->handle($request);
    }

    public function testHandleWithInvalidGeneratorKey(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No generator configured for key "test_key".');

        $handler = new EmbeddingsStoreHandler(
            [],
            [],
            [],
            [TestEmbedding::class => 'test_key'],
        );

        $request = new EmbeddingsStoreRequest(
            function () {},
            [new TestEmbedding('test-id', 'test content')],
        );

        $handler->handle($request);
    }

    public function testHandleWithInvalidStoreKey(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No store configured for key "test_key".');

        $key = 'test_key';
        $vector = [0.1, 0.2, 0.3];
        $headerGenerator = fn () => ['header' => 'value'];

        $embedding = new TestEmbedding('test-id', 'test content');
        $generatedEmbedding = new TestEmbedding('test-id', 'processed content');

        $generator = $this->prophesize(EmbeddingGeneratorInterface::class);
        $adapter = $this->prophesize(EmbeddingAdapterInterface::class);

        $generator->generateEmbedding($embedding, $headerGenerator)->willReturn([$generatedEmbedding]);

        $embedResponse = new EmbedResponse($vector, new EmbeddingUsage(10, 20));
        $adapter->embed(Argument::any())->willReturn($embedResponse);

        $handler = new EmbeddingsStoreHandler(
            [$key => $generator->reveal()],
            [],
            [$key => $adapter->reveal()],
            [TestEmbedding::class => $key],
        );

        $request = new EmbeddingsStoreRequest(
            function () {},
            [$embedding],
            $headerGenerator,
        );

        $response = $handler->handle($request);

        $this->assertCount(1, $response->getEmbeddings());
        $this->assertSame($generatedEmbedding, $response->getEmbeddings()[0]);
    }

    public function testHandleWithInvalidAdapterKey(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No adapter configured for key "test_key".');

        $key = 'test_key';
        $generator = $this->prophesize(EmbeddingGeneratorInterface::class);
        $store = $this->prophesize(EmbeddingsStoreInterface::class);

        $handler = new EmbeddingsStoreHandler(
            [$key => $generator->reveal()],
            [$key => $store->reveal()],
            [],
            [TestEmbedding::class => $key],
        );

        $request = new EmbeddingsStoreRequest(
            function () {},
            [new TestEmbedding('test-id', 'test content')],
        );

        $handler->handle($request);
    }
}

class TestEmbedding implements EmbeddingInterface
{
    /**
     * @var float[]
     */
    private array $vector = [];
    private string $formattedContent = '';
    private int $chunkNumber = 0;

    public function __construct(
        private readonly string $identifier,
        private readonly string $content,
    ) {
        $this->formattedContent = $content;
    }

    /**
     * @param array{
     *     identifier: string,
     *     content: string,
     *     vector?: float[],
     *     formattedContent?: string,
     * } $data
     */
    public static function fromArray(array $data): self
    {
        $instance = new self(
            $data['identifier'],
            $data['content'],
        );

        if (isset($data['vector'])) {
            $instance->setVector($data['vector']);
        }

        if (isset($data['formattedContent'])) {
            $instance->setFormattedContent($data['formattedContent']);
        }

        return $instance;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function split(string $content, int $chunkNumber): self
    {
        $newInstance = new self($this->identifier . '-' . $chunkNumber, $content);
        $newInstance->chunkNumber = $chunkNumber;

        return $newInstance;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getFormattedContent(): string
    {
        return $this->formattedContent;
    }

    public function setFormattedContent(string $formattedContent): void
    {
        $this->formattedContent = $formattedContent;
    }

    public function getVector(): ?array
    {
        return $this->vector;
    }

    public function setVector(array $vector): void
    {
        $this->vector = $vector;
    }

    public function getHash(): string
    {
        return \md5($this->getContent());
    }

    public function getChunkNumber(): int
    {
        return $this->chunkNumber;
    }

    /**
     * @return array{
     *     identifier: string,
     *     content: string,
     *     formattedContent: string,
     *     vector: float[],
     *     chunkNumber: int,
     * }
     */
    public function toArray(): array
    {
        return [
            'identifier' => $this->identifier,
            'content' => $this->content,
            'formattedContent' => $this->formattedContent,
            'vector' => $this->vector,
            'chunkNumber' => $this->chunkNumber,
        ];
    }
}
