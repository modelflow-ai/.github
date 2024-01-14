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
use ModelflowAi\Ollama\ClientInterface;
use ModelflowAi\Ollama\Resources\EmbeddingsInterface;
use ModelflowAi\Ollama\Responses\Embeddings\CreateResponse;
use ModelflowAi\OllamaAdapter\Embeddings\OllamaEmbeddingAdapter;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class MistralEmbeddingAdapterTest extends TestCase
{
    use ProphecyTrait;

    public function testEmbedText(): void
    {
        $embedding = $this->prophesize(EmbeddingsInterface::class);
        $client = $this->prophesize(ClientInterface::class);
        $client->embeddings()->willReturn($embedding->reveal());

        $embedding->create([
            'model' => 'llama2',
            'prompt' => 'some text',
        ])->willReturn(CreateResponse::from([
            'embedding' => [0.1, 0.2, 0.3],
        ], MetaInformation::from([])));

        $adapter = new OllamaEmbeddingAdapter($client->reveal());
        $result = $adapter->embedText('some text');

        $this->assertSame([0.1, 0.2, 0.3], $result);
    }
}
