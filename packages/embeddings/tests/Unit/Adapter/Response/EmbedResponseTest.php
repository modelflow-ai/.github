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
    public function testConstruct(): void
    {
        $vector = [0.1, 0.2, 0.3];
        $usage = new EmbeddingUsage(10, 20);

        $response = new EmbedResponse($vector, $usage);

        $this->assertSame($vector, $response->getVector());
        $this->assertSame($usage, $response->getUsage());
    }
}
