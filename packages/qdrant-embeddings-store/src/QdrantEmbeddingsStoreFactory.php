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

namespace ModelflowAi\Embeddings\Store\Qdrant;

use ModelflowAi\Embeddings\Store\Dsn\Dsn;
use ModelflowAi\Embeddings\Store\EmbeddingsStoreFactoryInterface;
use ModelflowAi\Embeddings\Store\EmbeddingsStoreInterface;
use Qdrant\Config;
use Qdrant\Http\GuzzleClient;
use Qdrant\Qdrant;
use Webmozart\Assert\Assert;

class QdrantEmbeddingsStoreFactory implements EmbeddingsStoreFactoryInterface
{
    public function create(Dsn $dsn): EmbeddingsStoreInterface
    {
        $scheme = 'qdrants' === $dsn->scheme ? 'https' : 'http';

        $client = new Qdrant(new GuzzleClient(
            (new Config($scheme . '://' . $dsn->host, $dsn->port ?? 6333))->setApiKey($dsn->user ?? ''),
        ));

        /** @var int<1, max> $chunkSize */
        $chunkSize = $dsn->getOption('chunk_size', 600);
        Assert::integer($chunkSize, 'The chunk size must be an integer.');
        Assert::greaterThan($chunkSize, 0, 'The chunk size must be greater than 0.');

        return new QdrantEmbeddingsStore($client, $dsn->path ?? 'default', $chunkSize);
    }

    public function supports(Dsn $dsn): bool
    {
        return 'qdrant' === $dsn->scheme
            || 'qdrants' === $dsn->scheme;
    }
}
