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

namespace ModelflowAi\MistralAdapter\Tests\Unit\Embeddings;

use ModelflowAi\Mistral\ClientInterface;
use ModelflowAi\Mistral\Model;
use ModelflowAi\Mistral\Resources\EmbeddingsInterface;
use ModelflowAi\Mistral\Responses\Embeddings\CreateResponse;
use ModelflowAi\Mistral\Responses\MetaInformation;
use ModelflowAi\MistralAdapter\Embeddings\MistralEmbeddingAdapter;
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
            'model' => Model::EMBED->value,
            'input' => ['some text'],
        ])->willReturn(CreateResponse::from([
            'id' => 'embd-aad6fc62b17349b192ef09225058bc45',
            'object' => 'list',
            'data' => [
                [
                    'object' => 'embedding',
                    'embedding' => [0.1, 0.2, 0.3],
                    'index' => 0,
                ],
            ],
            'model' => Model::EMBED->value,
            'usage' => [
                'prompt_tokens' => 9,
                'total_tokens' => 9,
            ],
        ], MetaInformation::from([])));

        $adapter = new MistralEmbeddingAdapter($client->reveal());
        $result = $adapter->embedText('some text');

        $this->assertSame([0.1, 0.2, 0.3], $result);
    }
}
