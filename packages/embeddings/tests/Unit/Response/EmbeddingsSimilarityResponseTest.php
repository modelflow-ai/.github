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

namespace ModelflowAi\Embeddings\Tests\Unit\Response;

use ModelflowAi\Embeddings\Model\EmbeddingInterface;
use ModelflowAi\Embeddings\Response\EmbeddingsSimilarityResponse;
use ModelflowAi\Embeddings\Usage\EmbeddingUsage;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class EmbeddingsSimilarityResponseTest extends TestCase
{
    use ProphecyTrait;

    public function testConstruct(): void
    {
        $embeddings = [
            $this->prophesize(EmbeddingInterface::class)->reveal(),
            $this->prophesize(EmbeddingInterface::class)->reveal(),
        ];

        $usage = new EmbeddingUsage(10, 20);

        $response = new EmbeddingsSimilarityResponse($embeddings, $usage);

        $this->assertSame($embeddings, $response->getEmbeddings());
        $this->assertSame($usage, $response->getUsage());
    }
}
