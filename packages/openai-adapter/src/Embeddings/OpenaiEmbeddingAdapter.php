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

class OpenaiEmbeddingAdapter implements EmbeddingAdapterInterface
{
    public function __construct(
        private readonly Client $client,
        private readonly string $model = 'text-embedding-ada-002',
    ) {
    }

    public function embedText(string $text): array
    {
        $response = $this->client->embeddings()->create([
            'model' => $this->model,
            'input' => $text,
            'encoding_format' => 'float',
        ]);

        return $response->embeddings;
    }
}
