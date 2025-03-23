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

namespace ModelflowAi\Embeddings;

use ModelflowAi\Embeddings\Handler\EmbeddingsSimilarityHandlerInterface;
use ModelflowAi\Embeddings\Handler\EmbeddingsStoreHandlerInterface;
use ModelflowAi\Embeddings\Model\EmbeddingInterface;
use ModelflowAi\Embeddings\Request\EmbeddingsSimilarityRequest;
use ModelflowAi\Embeddings\Request\EmbeddingsStoreRequest;

final readonly class EmbeddingsRequestHandler implements EmbeddingsRequestHandlerInterface
{
    public function __construct(
        private EmbeddingsStoreHandlerInterface $storeHandler,
        private EmbeddingsSimilarityHandlerInterface $similarityHandler,
    ) {
    }

    public function createStoreRequest(EmbeddingInterface ...$embeddings): EmbeddingsStoreRequest
    {
        return new EmbeddingsStoreRequest(
            fn (EmbeddingsStoreRequest $request) => $this->storeHandler->handle($request),
            $embeddings,
        );
    }

    public function createSimilarityRequest(string $content, string $key): EmbeddingsSimilarityRequest
    {
        return new EmbeddingsSimilarityRequest(
            fn (EmbeddingsSimilarityRequest $request) => $this->similarityHandler->handle($request),
            $content,
            $key,
        );
    }
}
