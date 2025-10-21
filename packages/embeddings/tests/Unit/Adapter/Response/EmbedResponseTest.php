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

namespace ModelflowAi\Embeddings\Tests\Unit\Adapter\Response;

use ModelflowAi\Embeddings\Adapter\Response\EmbedResponse;
use ModelflowAi\Embeddings\Usage\EmbeddingUsage;
use PHPUnit\Framework\TestCase;

class EmbedResponseTest extends TestCase
{
    public function testConstructWithSingleVector(): void
    {
        $vectors = [[0.1, 0.2, 0.3]];
        $usage = new EmbeddingUsage(10, 20);

        $response = new EmbedResponse($vectors, $usage);

        $this->assertSame($vectors, $response->getVectors());
        $this->assertSame($usage, $response->getUsage());
        $this->assertSame(1, $response->count());
    }

    public function testConstructWithMultipleVectors(): void
    {
        $vectors = [
            [0.1, 0.2, 0.3],
            [0.4, 0.5, 0.6],
            [0.7, 0.8, 0.9],
        ];
        $usage = new EmbeddingUsage(30, 60);

        $response = new EmbedResponse($vectors, $usage);

        $this->assertSame($vectors, $response->getVectors());
        $this->assertSame($usage, $response->getUsage());
        $this->assertSame(3, $response->count());
    }

    public function testConstructWithEmptyArrayThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('EmbedResponse requires at least one vector.');

        new EmbedResponse([], new EmbeddingUsage(0, 0));
    }
}
