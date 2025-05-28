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

namespace ModelflowAi\AnthropicAdapter\Embeddings;

use ModelflowAi\Anthropic\ClientInterface;
use ModelflowAi\Embeddings\Adapter\DeprecatedEmbedTextTrait;
use ModelflowAi\Embeddings\Adapter\EmbeddingAdapterInterface;
use ModelflowAi\Embeddings\Adapter\Request\EmbedRequest;
use ModelflowAi\Embeddings\Adapter\Response\EmbedResponse;
use ModelflowAi\Embeddings\Usage\EmbeddingUsage;

final readonly class AnthropicEmbeddingAdapter implements EmbeddingAdapterInterface
{
    use DeprecatedEmbedTextTrait;

    public function __construct(
        private ClientInterface $client,
        private string $model = 'voyage-3',
    ) {
    }

    public function embed(EmbedRequest $request): EmbedResponse
    {
        $response = $this->client->embeddings()->create([
            'model' => $this->model,
            'input' => $request->getText(),
            'input_type' => 'document',
        ]);

        if ([] === $response->data) {
            throw new \RuntimeException('Could not embed text');
        }

        return new EmbedResponse(
            $response->data[0]->embedding,
            new EmbeddingUsage(
                $response->usage->totalTokens,
                $response->usage->totalTokens,
            ),
        );
    }

    public function embedText(string $text): array
    {
        $response = $this->client->embeddings()->create([
            'model' => $this->model,
            'input' => $text,
            'input_type' => 'document',
        ]);

        if ([] === $response->data) {
            throw new \RuntimeException('Could not embed text');
        }

        return $response->data[0]->embedding;
    }
}
