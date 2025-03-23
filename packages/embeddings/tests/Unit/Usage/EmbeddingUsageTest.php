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

namespace ModelflowAi\Embeddings\Tests\Unit\Usage;

use ModelflowAi\Embeddings\Usage\EmbeddingUsage;
use PHPUnit\Framework\TestCase;

class EmbeddingUsageTest extends TestCase
{
    public function testConstruct(): void
    {
        $usage = new EmbeddingUsage(10, 20);

        $this->assertSame(10, $usage->getPromptTokens());
        $this->assertSame(20, $usage->getTotalTokens());
    }

    public function testConstructWithoutTotalTokens(): void
    {
        $usage = new EmbeddingUsage(10);

        $this->assertSame(10, $usage->getPromptTokens());
        $this->assertSame(10, $usage->getTotalTokens());
    }

    public function testFromUsages(): void
    {
        $usage1 = new EmbeddingUsage(10, 20);
        $usage2 = new EmbeddingUsage(15, 25);

        $combinedUsage = EmbeddingUsage::fromUsages([$usage1, $usage2]);

        $this->assertSame(25, $combinedUsage->getPromptTokens());
        $this->assertSame(45, $combinedUsage->getTotalTokens());
    }

    public function testFromEmptyUsages(): void
    {
        $combinedUsage = EmbeddingUsage::fromUsages([]);

        $this->assertSame(0, $combinedUsage->getPromptTokens());
        $this->assertSame(0, $combinedUsage->getTotalTokens());
    }

    public function testEmpty(): void
    {
        $combinedUsage = EmbeddingUsage::empty();

        $this->assertSame(0, $combinedUsage->getPromptTokens());
        $this->assertSame(0, $combinedUsage->getTotalTokens());
    }
}
