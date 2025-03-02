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

namespace ModelflowAi\Embeddings\Store;

use ModelflowAi\Embeddings\Store\Dsn\Dsn;

interface EmbeddingsStoreFactoryInterface
{
    public function create(Dsn $dsn): EmbeddingsStoreInterface;

    public function supports(Dsn $dsn): bool;
}
