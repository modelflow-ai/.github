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

namespace ModelflowAi\AnthropicAdapter\Tests\Unit\Embeddings;

use ModelflowAi\Anthropic\ClientInterface;
use ModelflowAi\AnthropicAdapter\Embeddings\AnthropicEmbeddingAdapter;
use ModelflowAi\AnthropicAdapter\Embeddings\AnthropicEmbeddingsAdapterFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class AnthropicEmbeddingsAdapterFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testCreateEmbeddingAdapter(): void
    {
        $client = $this->prophesize(ClientInterface::class);
        $factory = new AnthropicEmbeddingsAdapterFactory($client->reveal());

        $adapter = $factory->createEmbeddingAdapter([]);

        $this->assertInstanceOf(AnthropicEmbeddingAdapter::class, $adapter);
    }

    public function testCreateEmbeddingAdapterWithCustomModel(): void
    {
        $client = $this->prophesize(ClientInterface::class);
        $factory = new AnthropicEmbeddingsAdapterFactory($client->reveal());

        $adapter = $factory->createEmbeddingAdapter(['model' => 'voyage-3-large']);

        $this->assertInstanceOf(AnthropicEmbeddingAdapter::class, $adapter);
    }
}
