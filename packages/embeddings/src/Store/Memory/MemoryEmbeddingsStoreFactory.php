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

namespace ModelflowAi\Embeddings\Store\Memory;

use ModelflowAi\Embeddings\Store\Dsn\Dsn;
use ModelflowAi\Embeddings\Store\EmbeddingsStoreFactoryInterface;
use ModelflowAi\Embeddings\Store\EmbeddingsStoreInterface;

class MemoryEmbeddingsStoreFactory implements EmbeddingsStoreFactoryInterface
{
    public function create(Dsn $dsn): EmbeddingsStoreInterface
    {
        return new MemoryEmbeddingsStore();
    }

    public function supports(Dsn $dsn): bool
    {
        return 'memory' === $dsn->scheme;
    }
}
