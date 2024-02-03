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

namespace ModelflowAi\OllamaAdapter\Tests\Unit\Model;

use ModelflowAi\Ollama\ClientInterface;
use ModelflowAi\OllamaAdapter\Embeddings\OllamaEmbeddingAdapter;
use ModelflowAi\OllamaAdapter\Model\OllamaChatModelAdapter;
use ModelflowAi\OllamaAdapter\Model\OllamaTextModelAdapter;
use ModelflowAi\OllamaAdapter\OllamaAdapterFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class OllamaAdapterFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testCreateChatAdapter(): void
    {
        $client = $this->prophesize(ClientInterface::class);

        $factory = new OllamaAdapterFactory($client->reveal());

        $adapter = $factory->createChatAdapter([
            'model' => 'llama2',
            'image_to_text' => true,
            'functions' => true,
            'priority' => 0,
        ]);
        $this->assertInstanceOf(OllamaChatModelAdapter::class, $adapter);
    }

    public function testCreateTextAdapter(): void
    {
        $client = $this->prophesize(ClientInterface::class);

        $factory = new OllamaAdapterFactory($client->reveal());

        $adapter = $factory->createTextAdapter([
            'model' => 'llama2',
            'image_to_text' => true,
            'functions' => true,
            'priority' => 0,
        ]);
        $this->assertInstanceOf(OllamaTextModelAdapter::class, $adapter);
    }

    public function testCreateEmbeddingAdapter(): void
    {
        $client = $this->prophesize(ClientInterface::class);

        $factory = new OllamaAdapterFactory($client->reveal());

        $adapter = $factory->createEmbeddingAdapter([
            'model' => 'llama2',
        ]);
        $this->assertInstanceOf(OllamaEmbeddingAdapter::class, $adapter);
    }
}
