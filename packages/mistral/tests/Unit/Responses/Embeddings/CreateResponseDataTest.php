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

namespace ModelflowAi\Mistral\Tests\Unit\Responses\Embeddings;

use ModelflowAi\Mistral\Responses\Embeddings\CreateResponseData;
use ModelflowAi\Mistral\Tests\DataFixtures;
use PHPUnit\Framework\TestCase;

final class CreateResponseDataTest extends TestCase
{
    public function testFrom(): void
    {
        $instance = CreateResponseData::from(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['data'][0]);

        $this->assertInstanceOf(CreateResponseData::class, $instance);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['data'][0]['object'], $instance->object);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['data'][0]['embedding'], $instance->embedding);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['data'][0]['index'], $instance->index);
    }
}
