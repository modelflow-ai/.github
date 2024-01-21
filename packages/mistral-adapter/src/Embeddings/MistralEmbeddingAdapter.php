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

namespace ModelflowAi\MistralAdapter\Embeddings;

use ModelflowAi\Core\Embeddings\EmbeddingAdapterInterface;
use ModelflowAi\Mistral\ClientInterface;
use ModelflowAi\Mistral\Model;

final readonly class MistralEmbeddingAdapter implements EmbeddingAdapterInterface
{
    public function __construct(
        private ClientInterface $client,
        private Model $model = Model::EMBED,
    ) {
    }

    public function embedText(string $text): array
    {
        $response = $this->client->embeddings()->create([
            'model' => $this->model,
            'input' => [$text],
        ]);

        return $response->data[0]->embedding;
    }
}
