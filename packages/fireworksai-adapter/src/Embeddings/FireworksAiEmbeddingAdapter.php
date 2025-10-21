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

namespace ModelflowAi\FireworksAiAdapter\Embeddings;

use ModelflowAi\Embeddings\Adapter\EmbeddingAdapterInterface;
use ModelflowAi\Embeddings\Adapter\Request\EmbedRequest;
use ModelflowAi\Embeddings\Adapter\Response\EmbedResponse;
use ModelflowAi\Embeddings\Usage\EmbeddingUsage;
use OpenAI\Contracts\ClientContract;

final readonly class FireworksAiEmbeddingAdapter implements EmbeddingAdapterInterface
{
    public function __construct(
        private ClientContract $client,
        private string $model = 'nomic-ai/nomic-embed-text-v1.5',
    ) {
    }

    public function embed(EmbedRequest $request): EmbedResponse
    {
        $response = $this->client->embeddings()->create([
            'model' => $this->model,
            'input' => $request->getTexts(),
            'encoding_format' => 'float',
        ]);

        if ([] === $response->embeddings) {
            throw new \RuntimeException('Could not embed text');
        }

        $vectors = [];
        foreach ($response->embeddings as $item) {
            $vectors[] = $item->embedding;
        }

        return new EmbedResponse(
            $vectors,
            new EmbeddingUsage(
                $response->usage->promptTokens,
                $response->usage->totalTokens,
            ),
        );
    }
}
