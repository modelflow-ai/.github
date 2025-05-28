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
use ModelflowAi\Anthropic\Resources\EmbeddingsInterface;
use ModelflowAi\Anthropic\Responses\Embeddings\CreateResponse;
use ModelflowAi\Anthropic\Responses\Embeddings\EmbeddingData;
use ModelflowAi\Anthropic\Responses\Embeddings\Usage;
use ModelflowAi\AnthropicAdapter\Embeddings\AnthropicEmbeddingAdapter;
use ModelflowAi\Embeddings\Adapter\Request\EmbedRequest;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class AnthropicEmbeddingAdapterTest extends TestCase
{
    use ProphecyTrait;

    public function testEmbedText(): void
    {
        $embeddings = $this->prophesize(EmbeddingsInterface::class);
        $client = $this->prophesize(ClientInterface::class);
        $client->embeddings()->willReturn($embeddings->reveal());

        $embeddings->create([
            'model' => 'voyage-3',
            'input' => 'some text',
            'input_type' => 'document',
        ])->willReturn(new CreateResponse(
            'list',
            [
                new EmbeddingData(
                    'embedding',
                    [-0.008906792, -0.013743395],
                    0,
                ),
            ],
            'voyage-3',
            new Usage(8),
        ));

        $adapter = new AnthropicEmbeddingAdapter($client->reveal());
        $result = $adapter->embedText('some text');

        $this->assertSame([
            -0.008906792,
            -0.013743395,
        ], $result);
    }

    public function testEmbed(): void
    {
        $embeddings = $this->prophesize(EmbeddingsInterface::class);
        $client = $this->prophesize(ClientInterface::class);
        $client->embeddings()->willReturn($embeddings->reveal());

        $embeddings->create([
            'model' => 'voyage-3',
            'input' => 'some text',
            'input_type' => 'document',
        ])->willReturn(new CreateResponse(
            'list',
            [
                new EmbeddingData(
                    'embedding',
                    [-0.008906792, -0.013743395],
                    0,
                ),
            ],
            'voyage-3',
            new Usage(8),
        ));

        $adapter = new AnthropicEmbeddingAdapter($client->reveal());
        $request = new EmbedRequest('some text');
        $response = $adapter->embed($request);

        $this->assertSame([
            -0.008906792,
            -0.013743395,
        ], $response->getVector());
        $this->assertSame(8, $response->getUsage()->getPromptTokens());
        $this->assertSame(8, $response->getUsage()->getTotalTokens());
    }

    public function testEmbedWithCustomModel(): void
    {
        $embeddings = $this->prophesize(EmbeddingsInterface::class);
        $client = $this->prophesize(ClientInterface::class);
        $client->embeddings()->willReturn($embeddings->reveal());

        $embeddings->create([
            'model' => 'voyage-3-large',
            'input' => 'some text',
            'input_type' => 'document',
        ])->willReturn(new CreateResponse(
            'list',
            [
                new EmbeddingData(
                    'embedding',
                    [-0.008906792, -0.013743395],
                    0,
                ),
            ],
            'voyage-3-large',
            new Usage(8),
        ));

        $adapter = new AnthropicEmbeddingAdapter($client->reveal(), 'voyage-3-large');
        $request = new EmbedRequest('some text');
        $response = $adapter->embed($request);

        $this->assertSame([
            -0.008906792,
            -0.013743395,
        ], $response->getVector());
        $this->assertSame(8, $response->getUsage()->getPromptTokens());
        $this->assertSame(8, $response->getUsage()->getTotalTokens());
    }

    public function testEmbedThrowsExceptionWhenNoData(): void
    {
        $embeddings = $this->prophesize(EmbeddingsInterface::class);
        $client = $this->prophesize(ClientInterface::class);
        $client->embeddings()->willReturn($embeddings->reveal());

        $embeddings->create([
            'model' => 'voyage-3',
            'input' => 'some text',
            'input_type' => 'document',
        ])->willReturn(new CreateResponse(
            'list',
            [],
            'voyage-3',
            new Usage(8),
        ));

        $adapter = new AnthropicEmbeddingAdapter($client->reveal());
        $request = new EmbedRequest('some text');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not embed text');

        $adapter->embed($request);
    }
}
