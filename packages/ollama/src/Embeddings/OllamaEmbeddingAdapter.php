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

namespace ModelflowAi\Ollama\Embeddings;

use ModelflowAi\Core\Embeddings\EmbeddingAdapterInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OllamaEmbeddingAdapter implements EmbeddingAdapterInterface
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly string $model = 'llama2',
        private readonly string $url = 'http://localhost:11434/api',
    ) {
        \ini_set('default_socket_timeout', -1);
    }

    public function embedText(string $text): array
    {
        $response = $this->client->request('POST', $this->url . '/embeddings', [
            'json' => [
                'model' => $this->model,
                'prompt' => $text,
                'stream' => false,
            ],
        ]);

        $content = \json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        return $content['embedding'];
    }
}
