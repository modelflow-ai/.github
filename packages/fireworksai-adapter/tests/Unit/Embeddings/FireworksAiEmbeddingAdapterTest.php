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

namespace ModelflowAi\FireworksAiAdapter\Tests\Unit\Embeddings;

use ModelflowAi\Embeddings\Adapter\Request\EmbedRequest;
use ModelflowAi\FireworksAiAdapter\Embeddings\FireworksAiEmbeddingAdapter;
use OpenAI\Contracts\ClientContract;
use OpenAI\Contracts\Resources\EmbeddingsContract;
use OpenAI\Responses\Embeddings\CreateResponse;
use OpenAI\Responses\Meta\MetaInformation;
use OpenAI\Testing\Responses\Fixtures\Embeddings\CreateResponseFixture;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class FireworksAiEmbeddingAdapterTest extends TestCase
{
    use ProphecyTrait;

    public function testEmbed(): void
    {
        $embedding = $this->prophesize(EmbeddingsContract::class);
        $client = $this->prophesize(ClientContract::class);
        $client->embeddings()->willReturn($embedding->reveal());

        $embedding->create([
            'model' => 'nomic-ai/nomic-embed-text-v1.5',
            'input' => ['some text'],
            'encoding_format' => 'float',
        ])->willReturn(CreateResponse::from(
            CreateResponseFixture::ATTRIBUTES,
            MetaInformation::from([
                'x-request-id' => ['123'],
                'openai-model' => ['text-embedding-ada-002'],
                'openai-organization' => ['org'],
                'openai-version' => ['2021-10-10'],
                'openai-processing-ms' => ['123'],
                'x-ratelimit-limit-requests' => ['123'],
                'x-ratelimit-limit-tokens' => ['123'],
                'x-ratelimit-remaining-requests' => ['123'],
                'x-ratelimit-remaining-tokens' => ['123'],
                'x-ratelimit-reset-requests' => ['123'],
                'x-ratelimit-reset-tokens' => ['123'],
            ]),
        ));

        $adapter = new FireworksAiEmbeddingAdapter($client->reveal(), 'nomic-ai/nomic-embed-text-v1.5');
        $request = new EmbedRequest(['some text']);
        $response = $adapter->embed($request);

        $this->assertSame([[
            -0.008906792,
            -0.013743395,
        ]], $response->getVectors());
        $this->assertSame(8, $response->getUsage()->getPromptTokens());
        $this->assertSame(8, $response->getUsage()->getTotalTokens());
    }
}
