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

namespace ModelflowAi\Embeddings\Store\Filesystem;

use ModelflowAi\Embeddings\Store\Dsn\Dsn;
use ModelflowAi\Embeddings\Store\Dsn\InvalidDsnException;
use ModelflowAi\Embeddings\Store\EmbeddingsStoreFactoryInterface;
use ModelflowAi\Embeddings\Store\EmbeddingsStoreInterface;

class FilesystemEmbeddingsStoreFactory implements EmbeddingsStoreFactoryInterface
{
    public function create(Dsn $dsn): EmbeddingsStoreInterface
    {
        $path = $dsn->path;
        if (!\is_string($path)) {
            throw new InvalidDsnException('The path must be a string for file scheme DSNs.');
        }

        $directory = \dirname($path);
        if (!\is_dir($directory)) {
            throw new \InvalidArgumentException(\sprintf('The directory "%s" does not exist.', $directory)); // @codeCoverageIgnore
        }

        if (!\is_writable($directory)) {
            throw new \InvalidArgumentException(\sprintf('The directory "%s" is not writable.', $directory)); // @codeCoverageIgnore
        }

        return new FilesystemEmbeddingsStore($path);
    }

    public function supports(Dsn $dsn): bool
    {
        return 'file' === $dsn->scheme;
    }
}
