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
use ModelflowAi\Embeddings\Store\Filesystem\FilesystemEmbeddingsStore;
use ModelflowAi\Ollama\Ollama;
use ModelflowAi\OllamaAdapter\Embeddings\OllamaEmbeddingAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

require_once \dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/ExampleEmbedding.php';

$embeddingSplitter = new EmbeddingSplitter(500);
$embeddingFormatter = new EmbeddingFormatter();
$embeddingAdapter = new CacheEmbeddingAdapter(
    new OllamaEmbeddingAdapter(Ollama::client(), 'all-minilm'),
    new FilesystemAdapter('ollama', 0, __DIR__ . '/var/cache'),
);
$embeddingGenerator = new EmbeddingGenerator($embeddingSplitter, $embeddingFormatter);

if (\file_exists(__DIR__ . '/var/embeddings.txt')) {
    \unlink(__DIR__ . '/var/embeddings.txt');
}
$store = new FilesystemEmbeddingsStore(__DIR__ . '/var/embeddings.txt');

$embeddingClass = ExampleEmbedding::class;
$embeddingKey = 'example-store';

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

$embeddingsRequestHandler = new EmbeddingsRequestHandler(
    $storeHandler,
    $similarityHandler,
);

$embeddings = [
    new ExampleEmbedding(\file_get_contents(__DIR__ . '/var/books/schildbuerger.txt') ?: '', 'schildbuerger.txt'),
    new ExampleEmbedding(\file_get_contents(__DIR__ . '/var/books/nibelungenlied.txt') ?: '', 'nibelungenlied.txt'),
];

$storeResponse = $embeddingsRequestHandler
    ->createStoreRequest(...$embeddings)
    ->execute();

echo 'Store Response Usage: ' . $storeResponse->getUsage()->getPromptTokens() . ' prompt tokens / ' .
     $storeResponse->getUsage()->getTotalTokens() . " total tokens\n\n";

$similarityResponse = $embeddingsRequestHandler
    ->createSimilarityRequest('Welches Tier hat die Wittwe?', $embeddingKey)
    ->withLimit(4)
    ->withAdditionalFilter(['fileName' => 'schildbuerger.txt'])
    ->execute();

echo 'Similarity Response Usage: ' .
     $similarityResponse->getUsage()->getPromptTokens() .
     " prompt tokens\n\n";

foreach ($similarityResponse->getEmbeddings() as $item) {
    echo $item->getContent() . \PHP_EOL . \PHP_EOL;
}
