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

namespace App;

use ModelflowAi\Embeddings\Adapter\Cache\CacheEmbeddingAdapter;
use ModelflowAi\Embeddings\EmbeddingsRequestHandler;
use ModelflowAi\Embeddings\Formatter\EmbeddingFormatter;
use ModelflowAi\Embeddings\Generator\EmbeddingGenerator;
use ModelflowAi\Embeddings\Handler\EmbeddingsSimilarityHandler;
use ModelflowAi\Embeddings\Handler\EmbeddingsStoreHandler;
use ModelflowAi\Embeddings\Splitter\EmbeddingSplitter;
use ModelflowAi\Embeddings\Store\Memory\MemoryEmbeddingsStore;
use ModelflowAi\Mistral\Model;
use ModelflowAi\MistralAdapter\Embeddings\MistralEmbeddingAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

$mistralClient = require_once \dirname(__DIR__) . '/bootstrap.php';
require_once __DIR__ . '/ExampleEmbedding.php';

$embeddingSplitter = new EmbeddingSplitter(500);
$embeddingFormatter = new EmbeddingFormatter();
$embeddingAdapter = new CacheEmbeddingAdapter(
    new MistralEmbeddingAdapter($mistralClient, Model::EMBED->value),
    new FilesystemAdapter('mistral', 0, __DIR__ . '/var/cache'),
);
$embeddingGenerator = new EmbeddingGenerator($embeddingSplitter, $embeddingFormatter);

// Use memory store for this example
$store = new MemoryEmbeddingsStore();

$embeddingClass = ExampleEmbedding::class;
$embeddingKey = 'mistral-example-store';

$storeHandler = new EmbeddingsStoreHandler(
    [$embeddingKey => $embeddingGenerator],
    [$embeddingKey => $store],
    [$embeddingKey => $embeddingAdapter],
    [$embeddingClass => $embeddingKey],
);

$similarityHandler = new EmbeddingsSimilarityHandler(
    [$embeddingKey => $store],
    [$embeddingKey => $embeddingAdapter],
);

return new EmbeddingsRequestHandler(
    $storeHandler,
    $similarityHandler,
);
