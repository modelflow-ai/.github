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

namespace ModelflowAi\OpenaiAdapter\Tests\Unit\Model;

use ModelflowAi\OpenaiAdapter\Embeddings\OpenaiEmbeddingAdapter;
use ModelflowAi\OpenaiAdapter\Model\OpenaiChatModelAdapter;
use ModelflowAi\OpenaiAdapter\OpenaiAdapterFactory;
use OpenAI\Contracts\ClientContract;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class OpenaiAdapterFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testCreateChatAdapter(): void
    {
        $client = $this->prophesize(ClientContract::class);

        $factory = new OpenaiAdapterFactory($client->reveal());

        $adapter = $factory->createChatAdapter([
            'model' => 'gpt-4',
            'image_to_text' => true,
            'functions' => true,
            'priority' => 0,
        ]);
        $this->assertInstanceOf(OpenaiChatModelAdapter::class, $adapter);
    }

    public function testCreateEmbeddingAdapter(): void
    {
        $client = $this->prophesize(ClientContract::class);

        $factory = new OpenaiAdapterFactory($client->reveal());

        $adapter = $factory->createEmbeddingAdapter([
            'model' => 'gpt-4',
        ]);
        $this->assertInstanceOf(OpenaiEmbeddingAdapter::class, $adapter);
    }
}
