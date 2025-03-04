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

namespace ModelflowAi\Embeddings\Tests\Unit\Formatter;

use ModelflowAi\Embeddings\Formatter\EmbeddingFormatter;
use ModelflowAi\Embeddings\Model\EmbeddingInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class EmbeddingFormatterTest extends TestCase
{
    use ProphecyTrait;

    private EmbeddingFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new EmbeddingFormatter();
    }

    public function testFormatEmbeddingWithoutHeader(): void
    {
        /** @var ObjectProphecy<EmbeddingInterface> $embedding */
        $embedding = $this->prophesize(EmbeddingInterface::class);
        $content = 'This is the original content';

        $embedding->getContent()->willReturn($content);
        $embedding->setFormattedContent($content)->shouldBeCalled();

        $result = $this->formatter->formatEmbedding($embedding->reveal());

        $this->assertSame($embedding->reveal(), $result);
    }

    public function testFormatEmbeddingWithHeader(): void
    {
        /** @var ObjectProphecy<EmbeddingInterface> $embedding */
        $embedding = $this->prophesize(EmbeddingInterface::class);
        $content = 'This is the original content';
        $header = 'Header: ';
        $expectedFormattedContent = $header . $content;

        $embedding->getContent()->willReturn($content);
        $embedding->setFormattedContent($expectedFormattedContent)->shouldBeCalled();

        $result = $this->formatter->formatEmbedding($embedding->reveal(), $header);

        $this->assertSame($embedding->reveal(), $result);
    }

    public function testFormatEmbeddingWithEmptyContent(): void
    {
        /** @var ObjectProphecy<EmbeddingInterface> $embedding */
        $embedding = $this->prophesize(EmbeddingInterface::class);
        $content = '';
        $header = 'Header: ';
        $expectedFormattedContent = $header . $content;

        $embedding->getContent()->willReturn($content);
        $embedding->setFormattedContent($expectedFormattedContent)->shouldBeCalled();

        $result = $this->formatter->formatEmbedding($embedding->reveal(), $header);

        $this->assertSame($embedding->reveal(), $result);
    }

    public function testFormatEmbeddingsWithoutHeader(): void
    {
        /** @var ObjectProphecy<EmbeddingInterface> $embedding1 */
        $embedding1 = $this->prophesize(EmbeddingInterface::class);
        $content1 = 'Content 1';

        /** @var ObjectProphecy<EmbeddingInterface> $embedding2 */
        $embedding2 = $this->prophesize(EmbeddingInterface::class);
        $content2 = 'Content 2';

        $embedding1->getContent()->willReturn($content1);
        $embedding1->setFormattedContent($content1)->shouldBeCalled();

        $embedding2->getContent()->willReturn($content2);
        $embedding2->setFormattedContent($content2)->shouldBeCalled();

        $embeddings = [
            $embedding1->reveal(),
            $embedding2->reveal(),
        ];

        $result = $this->formatter->formatEmbeddings($embeddings);

        $this->assertCount(2, $result);
        $this->assertSame($embedding1->reveal(), $result[0]);
        $this->assertSame($embedding2->reveal(), $result[1]);
    }

    public function testFormatEmbeddingsWithHeader(): void
    {
        /** @var ObjectProphecy<EmbeddingInterface> $embedding1 */
        $embedding1 = $this->prophesize(EmbeddingInterface::class);
        $content1 = 'Content 1';

        /** @var ObjectProphecy<EmbeddingInterface> $embedding2 */
        $embedding2 = $this->prophesize(EmbeddingInterface::class);
        $content2 = 'Content 2';

        $header = 'Document: ';
        $expectedFormattedContent1 = $header . $content1;
        $expectedFormattedContent2 = $header . $content2;

        $embedding1->getContent()->willReturn($content1);
        $embedding1->setFormattedContent($expectedFormattedContent1)->shouldBeCalled();

        $embedding2->getContent()->willReturn($content2);
        $embedding2->setFormattedContent($expectedFormattedContent2)->shouldBeCalled();

        $embeddings = [
            $embedding1->reveal(),
            $embedding2->reveal(),
        ];

        $result = $this->formatter->formatEmbeddings($embeddings, $header);

        $this->assertCount(2, $result);
        $this->assertSame($embedding1->reveal(), $result[0]);
        $this->assertSame($embedding2->reveal(), $result[1]);
    }

    public function testFormatEmbeddingsWithEmptyArray(): void
    {
        $result = $this->formatter->formatEmbeddings([]);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testFormatEmbeddingsPreservesKeys(): void
    {
        /** @var ObjectProphecy<EmbeddingInterface> $embedding1 */
        $embedding1 = $this->prophesize(EmbeddingInterface::class);
        $content1 = 'Content 1';

        /** @var ObjectProphecy<EmbeddingInterface> $embedding2 */
        $embedding2 = $this->prophesize(EmbeddingInterface::class);
        $content2 = 'Content 2';

        $embedding1->getContent()->willReturn($content1);
        $embedding1->setFormattedContent($content1)->shouldBeCalled();

        $embedding2->getContent()->willReturn($content2);
        $embedding2->setFormattedContent($content2)->shouldBeCalled();

        $embeddings = [
            $embedding1->reveal(),
            $embedding2->reveal(),
        ];

        $result = $this->formatter->formatEmbeddings($embeddings);

        $this->assertSame($embedding1->reveal(), $result[0]);
        $this->assertSame($embedding2->reveal(), $result[1]);
    }
}
