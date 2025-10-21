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

namespace ModelflowAi\OllamaAdapter\Tests\Unit\Embeddings;

use ModelflowAi\ApiClient\Responses\MetaInformation;
use ModelflowAi\Embeddings\Adapter\Request\EmbedRequest;
use ModelflowAi\Ollama\ClientInterface;
use ModelflowAi\Ollama\Resources\EmbeddingsInterface;
use ModelflowAi\Ollama\Responses\Embeddings\CreateResponse;
use ModelflowAi\OllamaAdapter\Embeddings\OllamaEmbeddingAdapter;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class OllamaEmbeddingAdapterTest extends TestCase
{
    use ProphecyTrait;

    public function testEmbed(): void
    {
        $embedding = $this->prophesize(EmbeddingsInterface::class);
        $client = $this->prophesize(ClientInterface::class);
        $client->embeddings()->willReturn($embedding->reveal());

        $response = CreateResponse::from([
            'embedding' => [0.1, 0.2, 0.3],
        ], MetaInformation::from([]));

        $embedding->create([
            'model' => 'all-minilm',
            'prompt' => 'some text',
        ])->willReturn($response);

        $adapter = new OllamaEmbeddingAdapter($client->reveal());
        $request = new EmbedRequest(['some text']);
        $response = $adapter->embed($request);

        $this->assertSame([[0.1, 0.2, 0.3]], $response->getVectors());
        $this->assertSame(0, $response->getUsage()->getPromptTokens());
        $this->assertSame(0, $response->getUsage()->getTotalTokens());
    }

    public function testEmbedWithCustomModel(): void
    {
        $embedding = $this->prophesize(EmbeddingsInterface::class);
        $client = $this->prophesize(ClientInterface::class);
        $client->embeddings()->willReturn($embedding->reveal());

        $response = CreateResponse::from([
            'embedding' => [0.1, 0.2, 0.3],
        ], MetaInformation::from([]));

        $embedding->create([
            'model' => 'custom-model',
            'prompt' => 'some text',
        ])->willReturn($response);

        $adapter = new OllamaEmbeddingAdapter($client->reveal(), 'custom-model');
        $request = new EmbedRequest(['some text']);
        $response = $adapter->embed($request);

        $this->assertSame([[0.1, 0.2, 0.3]], $response->getVectors());
    }
}
