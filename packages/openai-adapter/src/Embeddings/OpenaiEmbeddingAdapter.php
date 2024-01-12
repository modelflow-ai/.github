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

use ModelflowAi\Core\Embeddings\EmbeddingAdapterInterface;
use OpenAI\Client;
use OpenAI\Responses\Embeddings\CreateResponseEmbedding;

final readonly class OpenaiEmbeddingAdapter implements EmbeddingAdapterInterface
{
    public function __construct(
        private Client $client,
        private string $model = 'text-embedding-ada-002',
    ) {
    }

    public function embedText(string $text): array
    {
        $response = $this->client->embeddings()->create([
            'model' => $this->model,
            'input' => $text,
            'encoding_format' => 'float',
        ]);

        /** @var float[] $embeddings */
        $embeddings = \array_values(
            \array_map(
                fn (CreateResponseEmbedding $embedding) => \array_values($embedding->embedding),
                $response->embeddings,
            ),
        );

        return $embeddings;
    }
}
