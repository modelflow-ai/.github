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

namespace ModelflowAi\Embeddings\Tests\Unit\Generator;

use ModelflowAi\Embeddings\Adapter\EmbeddingAdapterInterface;
use ModelflowAi\Embeddings\Formatter\EmbeddingFormatterInterface;
use ModelflowAi\Embeddings\Generator\EmbeddingGenerator;
use ModelflowAi\Embeddings\Model\EmbeddingInterface;
use ModelflowAi\Embeddings\Splitter\EmbeddingSplitterInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class EmbeddingGeneratorTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<EmbeddingSplitterInterface>
     */
    private ObjectProphecy $splitter;

    /**
     * @var ObjectProphecy<EmbeddingFormatterInterface>
     */
    private ObjectProphecy $formatter;

    /**
     * @var ObjectProphecy<EmbeddingAdapterInterface>
     */
    private ObjectProphecy $adapter;

    private EmbeddingGenerator $generator;

    protected function setUp(): void
    {
        $this->splitter = $this->prophesize(EmbeddingSplitterInterface::class);
        $this->formatter = $this->prophesize(EmbeddingFormatterInterface::class);
        $this->adapter = $this->prophesize(EmbeddingAdapterInterface::class);

        $this->generator = new EmbeddingGenerator(
            $this->splitter->reveal(),
            $this->formatter->reveal(),
            $this->adapter->reveal(),
        );
    }

    public function testEmbedText(): void
    {
        $text = 'Sample text to embed';
        $vector = [0.1, 0.2, 0.3, 0.4, 0.5];

        $this->adapter->embedText($text)->willReturn($vector);

        $result = $this->generator->embedText($text);

        $this->assertSame($vector, $result);
    }

    public function testGenerateEmbeddingWithoutHeaderGenerator(): void
    {
        /** @var ObjectProphecy<EmbeddingInterface> $originalEmbedding */
        $originalEmbedding = $this->prophesize(EmbeddingInterface::class);

        /** @var ObjectProphecy<EmbeddingInterface> $splitEmbedding */
        $splitEmbedding = $this->prophesize(EmbeddingInterface::class);

        $formattedContent = 'Formatted content';
        $splitEmbedding->getContent()->willReturn($formattedContent);

        $vector = [0.1, 0.2, 0.3];

        $this->splitter->splitEmbedding($originalEmbedding->reveal())->willReturn([$splitEmbedding->reveal()]);
        $this->formatter->formatEmbedding($splitEmbedding->reveal(), '')->willReturn($splitEmbedding->reveal());
        $this->adapter->embedText($formattedContent)->willReturn($vector);

        $splitEmbedding->setVector($vector)->shouldBeCalled();

        $result = $this->generator->generateEmbedding($originalEmbedding->reveal());

        $this->assertCount(1, $result);
        $this->assertSame($splitEmbedding->reveal(), $result[0]);
    }

    public function testGenerateEmbeddingWithHeaderGenerator(): void
    {
        /** @var ObjectProphecy<EmbeddingInterface> $originalEmbedding */
        $originalEmbedding = $this->prophesize(EmbeddingInterface::class);

        /** @var ObjectProphecy<EmbeddingInterface> $splitEmbedding */
        $splitEmbedding = $this->prophesize(EmbeddingInterface::class);

        $formattedContent = 'Formatted content';
        $splitEmbedding->getContent()->willReturn($formattedContent);

        $vector = [0.1, 0.2, 0.3];
        $headerText = 'Generated header: ';

        $headerGenerator = function (EmbeddingInterface $embedding) use ($originalEmbedding, $headerText) {
            $this->assertSame($originalEmbedding->reveal(), $embedding);

            return $headerText;
        };

        $this->splitter->splitEmbedding($originalEmbedding->reveal())->willReturn([$splitEmbedding->reveal()]);
        $this->formatter->formatEmbedding($splitEmbedding->reveal(), $headerText)->willReturn($splitEmbedding->reveal());
        $this->adapter->embedText($formattedContent)->willReturn($vector);

        $splitEmbedding->setVector($vector)->shouldBeCalled();

        $result = $this->generator->generateEmbedding($originalEmbedding->reveal(), $headerGenerator);

        $this->assertCount(1, $result);
        $this->assertSame($splitEmbedding->reveal(), $result[0]);
    }

    public function testGenerateEmbeddingWithMultipleSplits(): void
    {
        /** @var ObjectProphecy<EmbeddingInterface> $originalEmbedding */
        $originalEmbedding = $this->prophesize(EmbeddingInterface::class);

        /** @var ObjectProphecy<EmbeddingInterface> $splitEmbedding1 */
        $splitEmbedding1 = $this->prophesize(EmbeddingInterface::class);
        /** @var ObjectProphecy<EmbeddingInterface> $splitEmbedding2 */
        $splitEmbedding2 = $this->prophesize(EmbeddingInterface::class);

        $formattedContent1 = 'Formatted content 1';
        $formattedContent2 = 'Formatted content 2';
        $splitEmbedding1->getContent()->willReturn($formattedContent1);
        $splitEmbedding2->getContent()->willReturn($formattedContent2);

        $vector1 = [0.1, 0.2, 0.3];
        $vector2 = [0.4, 0.5, 0.6];

        $this->splitter->splitEmbedding($originalEmbedding->reveal())->willReturn([
            $splitEmbedding1->reveal(),
            $splitEmbedding2->reveal(),
        ]);

        $this->formatter->formatEmbedding($splitEmbedding1->reveal(), '')->willReturn($splitEmbedding1->reveal());
        $this->formatter->formatEmbedding($splitEmbedding2->reveal(), '')->willReturn($splitEmbedding2->reveal());

        $this->adapter->embedText($formattedContent1)->willReturn($vector1);
        $this->adapter->embedText($formattedContent2)->willReturn($vector2);

        $splitEmbedding1->setVector($vector1)->shouldBeCalled();
        $splitEmbedding2->setVector($vector2)->shouldBeCalled();

        $result = $this->generator->generateEmbedding($originalEmbedding->reveal());

        $this->assertCount(2, $result);
        $this->assertSame($splitEmbedding1->reveal(), $result[0]);
        $this->assertSame($splitEmbedding2->reveal(), $result[1]);
    }

    public function testGenerateEmbeddings(): void
    {
        /** @var ObjectProphecy<EmbeddingInterface> $embedding1 */
        $embedding1 = $this->prophesize(EmbeddingInterface::class);
        /** @var ObjectProphecy<EmbeddingInterface> $embedding2 */
        $embedding2 = $this->prophesize(EmbeddingInterface::class);

        /** @var ObjectProphecy<EmbeddingInterface> $splitEmbedding1 */
        $splitEmbedding1 = $this->prophesize(EmbeddingInterface::class);
        /** @var ObjectProphecy<EmbeddingInterface> $splitEmbedding2 */
        $splitEmbedding2 = $this->prophesize(EmbeddingInterface::class);

        $formattedContent1 = 'Formatted content 1';
        $formattedContent2 = 'Formatted content 2';
        $splitEmbedding1->getContent()->willReturn($formattedContent1);
        $splitEmbedding2->getContent()->willReturn($formattedContent2);

        $vector1 = [0.1, 0.2, 0.3];
        $vector2 = [0.4, 0.5, 0.6];

        $this->splitter->splitEmbedding($embedding1->reveal())->willReturn([$splitEmbedding1->reveal()]);
        $this->splitter->splitEmbedding($embedding2->reveal())->willReturn([$splitEmbedding2->reveal()]);

        $this->formatter->formatEmbedding($splitEmbedding1->reveal(), '')->willReturn($splitEmbedding1->reveal());
        $this->formatter->formatEmbedding($splitEmbedding2->reveal(), '')->willReturn($splitEmbedding2->reveal());

        $this->adapter->embedText($formattedContent1)->willReturn($vector1);
        $this->adapter->embedText($formattedContent2)->willReturn($vector2);

        $splitEmbedding1->setVector($vector1)->shouldBeCalled();
        $splitEmbedding2->setVector($vector2)->shouldBeCalled();

        $embeddings = [$embedding1->reveal(), $embedding2->reveal()];

        $result = $this->generator->generateEmbeddings($embeddings);

        $this->assertCount(2, $result);
        $this->assertSame($splitEmbedding1->reveal(), $result[0]);
        $this->assertSame($splitEmbedding2->reveal(), $result[1]);
    }

    public function testGenerateEmbeddingsWithHeaderGenerator(): void
    {
        /** @var ObjectProphecy<EmbeddingInterface> $embedding1 */
        $embedding1 = $this->prophesize(EmbeddingInterface::class);
        /** @var ObjectProphecy<EmbeddingInterface> $embedding2 */
        $embedding2 = $this->prophesize(EmbeddingInterface::class);

        /** @var ObjectProphecy<EmbeddingInterface> $splitEmbedding1 */
        $splitEmbedding1 = $this->prophesize(EmbeddingInterface::class);
        /** @var ObjectProphecy<EmbeddingInterface> $splitEmbedding2 */
        $splitEmbedding2 = $this->prophesize(EmbeddingInterface::class);

        $formattedContent1 = 'Formatted content 1';
        $formattedContent2 = 'Formatted content 2';
        $splitEmbedding1->getContent()->willReturn($formattedContent1);
        $splitEmbedding2->getContent()->willReturn($formattedContent2);

        $vector1 = [0.1, 0.2, 0.3];
        $vector2 = [0.4, 0.5, 0.6];
        $headerText = 'Generated header: ';

        $headerGenerator = fn (EmbeddingInterface $embedding) => $headerText . \spl_object_hash($embedding);

        $this->splitter->splitEmbedding($embedding1->reveal())->willReturn([$splitEmbedding1->reveal()]);
        $this->splitter->splitEmbedding($embedding2->reveal())->willReturn([$splitEmbedding2->reveal()]);

        $this->formatter->formatEmbedding($splitEmbedding1->reveal(), Argument::containingString($headerText))
            ->willReturn($splitEmbedding1->reveal());
        $this->formatter->formatEmbedding($splitEmbedding2->reveal(), Argument::containingString($headerText))
            ->willReturn($splitEmbedding2->reveal());

        $this->adapter->embedText($formattedContent1)->willReturn($vector1);
        $this->adapter->embedText($formattedContent2)->willReturn($vector2);

        $splitEmbedding1->setVector($vector1)->shouldBeCalled();
        $splitEmbedding2->setVector($vector2)->shouldBeCalled();

        $embeddings = [$embedding1->reveal(), $embedding2->reveal()];

        $result = $this->generator->generateEmbeddings($embeddings, $headerGenerator);

        $this->assertCount(2, $result);
        $this->assertSame($splitEmbedding1->reveal(), $result[0]);
        $this->assertSame($splitEmbedding2->reveal(), $result[1]);
    }

    public function testGenerateEmbeddingsWithEmptyArray(): void
    {
        $result = $this->generator->generateEmbeddings([]);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}
