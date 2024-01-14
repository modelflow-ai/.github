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

namespace ModelflowAi\Ollama\Tests\Unit\Responses\Embeddings;

use ModelflowAi\ApiClient\Responses\MetaInformation;
use ModelflowAi\Ollama\Responses\Embeddings\CreateResponse;
use ModelflowAi\Ollama\Tests\DataFixtures;
use PHPUnit\Framework\TestCase;

final class CreateResponseTest extends TestCase
{
    public function testFrom(): void
    {
        $meta = MetaInformation::from([]);

        $instance = CreateResponse::from(DataFixtures::EMBEDDINGS_CREATE_RESPONSE, $meta);

        $this->assertInstanceOf(CreateResponse::class, $instance);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['embedding'], $instance->embedding);
        $this->assertNull($instance->usage->completionTokens);
        $this->assertSame(0, $instance->usage->totalTokens);
        $this->assertInstanceOf(MetaInformation::class, $instance->meta);
    }
}
