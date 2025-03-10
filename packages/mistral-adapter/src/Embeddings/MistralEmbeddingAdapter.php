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

use ModelflowAi\Embeddings\Adapter\DeprecatedEmbedTextTrait;
use ModelflowAi\Embeddings\Adapter\EmbeddingAdapterInterface;
use ModelflowAi\Embeddings\Adapter\Request\EmbedRequest;
use ModelflowAi\Embeddings\Adapter\Response\EmbedResponse;
use ModelflowAi\Embeddings\Usage\EmbeddingUsage;
use ModelflowAi\Mistral\ClientInterface;
use ModelflowAi\Mistral\Model;

final readonly class MistralEmbeddingAdapter implements EmbeddingAdapterInterface
{
    use DeprecatedEmbedTextTrait;

    public function __construct(
        private ClientInterface $client,
        private string $model = Model::EMBED->value,
    ) {
    }

    public function embed(EmbedRequest $request): EmbedResponse
    {
        $response = $this->client->embeddings()->create([
            'model' => $this->model,
            'input' => [$request->getText()],
        ]);

        return new EmbedResponse(
            $response->data[0]->embedding,
            new EmbeddingUsage(
                $response->usage->promptTokens,
                $response->usage->totalTokens,
            ),
        );
    }
}
