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

use ModelflowAi\Embeddings\Model\EmbeddingInterface;
use ModelflowAi\Embeddings\Request\EmbeddingsSimilarityRequest;
use ModelflowAi\Embeddings\Request\EmbeddingsStoreRequest;

interface EmbeddingsRequestHandlerInterface
{
    public function createStoreRequest(EmbeddingInterface ...$embeddings): EmbeddingsStoreRequest;

    public function createSimilarityRequest(string $content, string $key): EmbeddingsSimilarityRequest;
}
