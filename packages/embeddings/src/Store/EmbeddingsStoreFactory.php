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

class EmbeddingsStoreFactory implements EmbeddingsStoreFactoryInterface
{
    /**
     * @param iterable<EmbeddingsStoreFactoryInterface> $factories
     */
    public function __construct(
        private readonly iterable $factories,
    ) {
    }

    public function create(Dsn|string $dsn): EmbeddingsStoreInterface
    {
        if (\is_string($dsn)) {
            $dsn = Dsn::fromString($dsn);
        }

        foreach ($this->factories as $factory) {
            if ($factory->supports($dsn)) {
                return $factory->create($dsn);
            }
        }

        throw new \InvalidArgumentException(
            \sprintf('No store supports the given DSN with scheme "%s".', $dsn->scheme),
        );
    }

    public function supports(Dsn|string $dsn): bool
    {
        if (\is_string($dsn)) {
            $dsn = Dsn::fromString($dsn);
        }

        foreach ($this->factories as $factory) {
            if ($factory->supports($dsn)) {
                return true;
            }
        }

        return false;
    }
}
