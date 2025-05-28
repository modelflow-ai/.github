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

namespace ModelflowAi\Anthropic\Resources;

use ModelflowAi\Anthropic\Responses\Embeddings\CreateResponse;
use ModelflowAi\ApiClient\Transport\TransportInterface;

final readonly class Embeddings implements EmbeddingsInterface
{
    public function __construct(
        private TransportInterface $transport,
    ) {
    }

    public function create(array $parameters): CreateResponse
    {
        $payload = $this->transport->req(
            'POST',
            'https://api.voyageai.com/v1/embeddings',
            [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->getVoyageApiKey(),
            ],
            $parameters,
        );

        return CreateResponse::from($payload);
    }

    private function getVoyageApiKey(): string
    {
        $apiKey = \getenv('VOYAGE_API_KEY');
        if (false === $apiKey) {
            throw new \RuntimeException('VOYAGE_API_KEY environment variable is required for embeddings');
        }

        return $apiKey;
    }
}
