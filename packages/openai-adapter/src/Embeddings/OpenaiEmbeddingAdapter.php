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

namespace ModelflowAi\OpenaiAdapter\Embeddings;

use ModelflowAi\Embeddings\Adapter\DeprecatedEmbedTextTrait;
use ModelflowAi\Embeddings\Adapter\EmbeddingAdapterInterface;
use ModelflowAi\Embeddings\Adapter\Request\EmbedRequest;
use ModelflowAi\Embeddings\Adapter\Response\EmbedResponse;
use ModelflowAi\Embeddings\Usage\EmbeddingUsage;
use OpenAI\Contracts\ClientContract;

final readonly class OpenaiEmbeddingAdapter implements EmbeddingAdapterInterface
{
    use DeprecatedEmbedTextTrait;

    public function __construct(
        private ClientContract $client,
        private string $model = 'text-embedding-ada-002',
    ) {
    }

    public function embed(EmbedRequest $request): EmbedResponse
    {
        $response = $this->client->embeddings()->create([
            'model' => $this->model,
            'input' => $request->getText(),
            'encoding_format' => 'float',
        ]);

        if ([] === $response->embeddings) {
            throw new \RuntimeException('Could not embed text');
        }

        return new EmbedResponse(
            $response->embeddings[0]->embedding,
            new EmbeddingUsage(
                $response->usage->promptTokens,
                $response->usage->totalTokens,
            ),
        );
    }

    public function embedText(string $text): array
    {
        $response = $this->client->embeddings()->create([
            'model' => $this->model,
            'input' => $text,
            'encoding_format' => 'float',
        ]);

        if ([] === $response->embeddings) {
            throw new \RuntimeException('Could not embed text');
        }

        return $response->embeddings[0]->embedding;
    }
}
