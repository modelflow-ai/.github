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
use ModelflowAi\Embeddings\Splitter\NoOpEmbeddingSplitter;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class NoOpEmbeddingSplitterTest extends TestCase
{
    use ProphecyTrait;

    public function testSplitEmbeddingReturnsOriginalEmbedding(): void
    {
        $splitter = new NoOpEmbeddingSplitter();

        /** @var ObjectProphecy<EmbeddingInterface> $embedding */
        $embedding = $this->prophesize(EmbeddingInterface::class);

        $result = $splitter->splitEmbedding($embedding->reveal());

        $this->assertCount(1, $result);
        $this->assertSame($embedding->reveal(), $result[0]);
    }

    public function testSplitEmbeddingsReturnsOriginalArray(): void
    {
        $splitter = new NoOpEmbeddingSplitter();

        /** @var ObjectProphecy<EmbeddingInterface> $embedding1 */
        $embedding1 = $this->prophesize(EmbeddingInterface::class);
        /** @var ObjectProphecy<EmbeddingInterface> $embedding2 */
        $embedding2 = $this->prophesize(EmbeddingInterface::class);

        $embeddings = [$embedding1->reveal(), $embedding2->reveal()];

        $result = $splitter->splitEmbeddings($embeddings);

        $this->assertCount(2, $result);
        $this->assertSame($embedding1->reveal(), $result[0]);
        $this->assertSame($embedding2->reveal(), $result[1]);
        $this->assertSame($embeddings, $result);
    }

    public function testSplitEmbeddingsWithEmptyArray(): void
    {
        $splitter = new NoOpEmbeddingSplitter();

        $result = $splitter->splitEmbeddings([]);

        $this->assertEmpty($result);
    }
}
