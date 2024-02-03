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

namespace ModelflowAi\MistralAdapter\Tests\Unit\Model;

use ModelflowAi\Mistral\ClientInterface;
use ModelflowAi\Mistral\Model;
use ModelflowAi\MistralAdapter\Embeddings\MistralEmbeddingAdapter;
use ModelflowAi\MistralAdapter\MistralAdapterFactory;
use ModelflowAi\MistralAdapter\Model\MistralChatModelAdapter;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class MistralAdapterFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testCreateChatAdapter(): void
    {
        $client = $this->prophesize(ClientInterface::class);

        $factory = new MistralAdapterFactory($client->reveal());

        $adapter = $factory->createChatAdapter([
            'model' => Model::MEDIUM->value,
            'image_to_text' => true,
            'functions' => true,
            'priority' => 0,
        ]);
        $this->assertInstanceOf(MistralChatModelAdapter::class, $adapter);
    }

    public function testCreateEmbeddingAdapter(): void
    {
        $client = $this->prophesize(ClientInterface::class);

        $factory = new MistralAdapterFactory($client->reveal());

        $adapter = $factory->createEmbeddingAdapter([
            'model' => Model::EMBED->value,
        ]);
        $this->assertInstanceOf(MistralEmbeddingAdapter::class, $adapter);
    }
}
