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

namespace ModelflowAi\Embeddings\Tests\Unit\Splitter;

use ModelflowAi\Embeddings\Model\EmbeddingInterface;
use ModelflowAi\Embeddings\Splitter\EmbeddingSplitter;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class EmbeddingSplitterTest extends TestCase
{
    use ProphecyTrait;

    public function testSplitEmbeddingWithEmptyText(): void
    {
        $splitter = new EmbeddingSplitter();

        /** @var ObjectProphecy<EmbeddingInterface> $embedding */
        $embedding = $this->prophesize(EmbeddingInterface::class);
        $embedding->getContent()->willReturn('');

        $result = $splitter->splitEmbedding($embedding->reveal());

        $this->assertEmpty($result);
    }

    public function testSplitEmbeddingWithTextAs0(): void
    {
        $splitter = new EmbeddingSplitter();

        /** @var ObjectProphecy<EmbeddingInterface> $embedding */
        $embedding = $this->prophesize(EmbeddingInterface::class);
        $embedding->getContent()->willReturn('0');

        $result = $splitter->splitEmbedding($embedding->reveal());

        $this->assertEmpty($result);
    }

    public function testSplitEmbeddingWithNegativeMaxLength(): void
    {
        $splitter = new EmbeddingSplitter(-10);

        /** @var ObjectProphecy<EmbeddingInterface> $embedding */
        $embedding = $this->prophesize(EmbeddingInterface::class);
        $embedding->getContent()->willReturn('Some content');

        $result = $splitter->splitEmbedding($embedding->reveal());

        $this->assertEmpty($result);
    }

    public function testSplitEmbeddingWithZeroMaxLength(): void
    {
        $splitter = new EmbeddingSplitter(0);

        /** @var ObjectProphecy<EmbeddingInterface> $embedding */
        $embedding = $this->prophesize(EmbeddingInterface::class);
        $embedding->getContent()->willReturn('Some content');

        $result = $splitter->splitEmbedding($embedding->reveal());

        $this->assertEmpty($result);
    }

    public function testSplitEmbeddingWithEmptySeparator(): void
    {
        $splitter = new EmbeddingSplitter(1000, '');

        /** @var ObjectProphecy<EmbeddingInterface> $embedding */
        $embedding = $this->prophesize(EmbeddingInterface::class);
        $embedding->getContent()->willReturn('Some content');

        $result = $splitter->splitEmbedding($embedding->reveal());

        $this->assertEmpty($result);
    }

    public function testSplitEmbeddingWithShortText(): void
    {
        $splitter = new EmbeddingSplitter(100);

        /** @var ObjectProphecy<EmbeddingInterface> $embedding */
        $embedding = $this->prophesize(EmbeddingInterface::class);
        $embedding->getContent()->willReturn('Short text');

        $result = $splitter->splitEmbedding($embedding->reveal());

        $this->assertCount(1, $result);
        $this->assertSame($embedding->reveal(), $result[0]);
    }

    public function testSplitEmbeddingWithLongText(): void
    {
        $maxLength = 20;
        $splitter = new EmbeddingSplitter($maxLength);

        $longText = 'This is a long text that needs to be split into multiple chunks because it exceeds the maximum length';

        /** @var ObjectProphecy<EmbeddingInterface> $embedding */
        $embedding = $this->prophesize(EmbeddingInterface::class);
        $embedding->getContent()->willReturn($longText);

        $chunk1 = $this->prophesize(EmbeddingInterface::class);
        $chunk2 = $this->prophesize(EmbeddingInterface::class);
        $chunk3 = $this->prophesize(EmbeddingInterface::class);
        $chunk4 = $this->prophesize(EmbeddingInterface::class);
        $chunk5 = $this->prophesize(EmbeddingInterface::class);
        $chunk6 = $this->prophesize(EmbeddingInterface::class);

        $embedding->split('This is a long text', 0)->willReturn($chunk1->reveal());
        $embedding->split('that needs to be', 1)->willReturn($chunk2->reveal());
        $embedding->split('split into multiple', 2)->willReturn($chunk3->reveal());
        $embedding->split('chunks because it', 3)->willReturn($chunk4->reveal());
        $embedding->split('exceeds the maximum', 4)->willReturn($chunk5->reveal());
        $embedding->split('length', 5)->willReturn($chunk6->reveal());

        $result = $splitter->splitEmbedding($embedding->reveal());

        $this->assertGreaterThan(1, \count($result));
        foreach ($result as $chunk) {
            $this->assertInstanceOf(EmbeddingInterface::class, $chunk);
        }
    }

    public function testSplitEmbeddingWithLongWordExceedingMaxLength(): void
    {
        $maxLength = 10;
        $splitter = new EmbeddingSplitter($maxLength);

        $text = 'Short VeryLongWordThatExceedsMaximumLength end';

        /** @var ObjectProphecy<EmbeddingInterface> $embedding */
        $embedding = $this->prophesize(EmbeddingInterface::class);
        $embedding->getContent()->willReturn($text);

        $chunk1 = $this->prophesize(EmbeddingInterface::class);
        $chunk2 = $this->prophesize(EmbeddingInterface::class);
        $chunk3 = $this->prophesize(EmbeddingInterface::class);

        $embedding->split('Short', 0)->willReturn($chunk1->reveal());
        $embedding->split('VeryLongWordThatExceedsMaximumLength', 1)->willReturn($chunk2->reveal());
        $embedding->split('end', 2)->willReturn($chunk3->reveal());

        $result = $splitter->splitEmbedding($embedding->reveal());

        $this->assertCount(3, $result);
        $this->assertSame($chunk1->reveal(), $result[0]);
        $this->assertSame($chunk2->reveal(), $result[1]);
        $this->assertSame($chunk3->reveal(), $result[2]);
    }

    public function testSplitEmbeddingWithCustomSeparator(): void
    {
        $maxLength = 15;
        $splitter = new EmbeddingSplitter($maxLength, ',');

        $text = 'One,Two,Three,Four,Five,Six';

        /** @var ObjectProphecy<EmbeddingInterface> $embedding */
        $embedding = $this->prophesize(EmbeddingInterface::class);
        $embedding->getContent()->willReturn($text);

        $chunk1 = $this->prophesize(EmbeddingInterface::class);
        $chunk2 = $this->prophesize(EmbeddingInterface::class);

        $embedding->split('One,Two,Three', 0)->willReturn($chunk1->reveal());
        $embedding->split('Four,Five,Six', 1)->willReturn($chunk2->reveal());

        $result = $splitter->splitEmbedding($embedding->reveal());

        $this->assertCount(2, $result);
        $this->assertSame($chunk1->reveal(), $result[0]);
        $this->assertSame($chunk2->reveal(), $result[1]);
    }

    public function testSplitEmbeddings(): void
    {
        $splitter = new EmbeddingSplitter(10);

        /** @var ObjectProphecy<EmbeddingInterface> $embedding1 */
        $embedding1 = $this->prophesize(EmbeddingInterface::class);
        $embedding1->getContent()->willReturn('Short text');

        /** @var ObjectProphecy<EmbeddingInterface> $embedding2 */
        $embedding2 = $this->prophesize(EmbeddingInterface::class);
        $embedding2->getContent()->willReturn('Another text that is longer');

        $chunk1 = $this->prophesize(EmbeddingInterface::class);
        $chunk2 = $this->prophesize(EmbeddingInterface::class);
        $chunk3 = $this->prophesize(EmbeddingInterface::class);

        $embedding2->split('Another', 0)->willReturn($chunk1->reveal());
        $embedding2->split('text that', 1)->willReturn($chunk2->reveal());
        $embedding2->split('is longer', 2)->willReturn($chunk3->reveal());

        $embeddings = [$embedding1->reveal(), $embedding2->reveal()];

        $result = $splitter->splitEmbeddings($embeddings);

        $this->assertCount(4, $result);
        $this->assertSame($embedding1->reveal(), $result[0]);
        $this->assertSame($chunk1->reveal(), $result[1]);
        $this->assertSame($chunk2->reveal(), $result[2]);
        $this->assertSame($chunk3->reveal(), $result[3]);
    }

    public function testSplitEmbeddingsWithEmptyArray(): void
    {
        $splitter = new EmbeddingSplitter();

        $result = $splitter->splitEmbeddings([]);

        $this->assertEmpty($result);
    }
}
