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

use ModelflowAi\ApiClient\Responses\MetaInformation;
use ModelflowAi\Mistral\Responses\Embeddings\CreateResponse;
use ModelflowAi\Mistral\Tests\DataFixtures;
use PHPUnit\Framework\TestCase;

final class CreateResponseTest extends TestCase
{
    public function testFrom(): void
    {
        $meta = MetaInformation::from([]);

        $instance = CreateResponse::from(DataFixtures::EMBEDDINGS_CREATE_RESPONSE, $meta);

        $this->assertInstanceOf(CreateResponse::class, $instance);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['id'], $instance->id);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['object'], $instance->object);
        $this->assertCount(\count(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['data']), $instance->data);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['data'][0]['object'], $instance->data[0]->object);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['data'][0]['embedding'], $instance->data[0]->embedding);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['data'][0]['index'], $instance->data[0]->index);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['data'][1]['object'], $instance->data[1]->object);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['data'][1]['embedding'], $instance->data[1]->embedding);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['data'][1]['index'], $instance->data[1]->index);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['model'], $instance->model);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['usage']['prompt_tokens'], $instance->usage->promptTokens);
        $this->assertNull($instance->usage->completionTokens);
        $this->assertSame(DataFixtures::EMBEDDINGS_CREATE_RESPONSE['usage']['total_tokens'], $instance->usage->totalTokens);
        $this->assertInstanceOf(MetaInformation::class, $instance->meta);
    }
}
