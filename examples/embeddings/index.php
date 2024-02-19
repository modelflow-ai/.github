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

use Elasticsearch\ClientBuilder;
use ModelflowAi\Embeddings\Adapter\Cache\CacheEmbeddingAdapter;
use ModelflowAi\Embeddings\Formatter\EmbeddingFormatter;
use ModelflowAi\Embeddings\Generator\EmbeddingGenerator;
use ModelflowAi\Embeddings\Splitter\EmbeddingSplitter;
use ModelflowAi\Embeddings\Store\Elasticsearch\ElasticsearchEmbeddingsStore;
use ModelflowAi\Ollama\Ollama;
use ModelflowAi\OllamaAdapter\Embeddings\OllamaEmbeddingAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/ExampleEmbedding.php';

$client = Ollama::client();

$embeddingSplitter = new EmbeddingSplitter(500);
$embeddingFormatter = new EmbeddingFormatter();
$embeddingAdapter = new CacheEmbeddingAdapter(
    new OllamaEmbeddingAdapter($client),
    new FilesystemAdapter('ollama', 0, __DIR__ . '/var/cache'),
);
$embeddingGenerator = new EmbeddingGenerator($embeddingSplitter, $embeddingFormatter, $embeddingAdapter);

$client = ClientBuilder::create()->build();
$client->indices()->delete(['index' => 'embeddings', 'ignore_unavailable' => true]);
$metadata = [
    'fileName' => [
        'type' => 'keyword',
    ],
];
$mapper = fn(ExampleEmbedding $embedding) => [
    'fileName' => $embedding->getFileName(),
];
$memoryStore = new ElasticsearchEmbeddingsStore($client, 'embeddings', $mapper, $metadata);

$input = [
    new ExampleEmbedding(\file_get_contents(__DIR__ . '/var/books/schildbuerger.txt') ?: '', 'schildbuerger.txt'),
    new ExampleEmbedding(\file_get_contents(__DIR__ . '/var/books/nibelungenlied.txt') ?: '', 'nibelungenlied.txt'),
];
$output = $embeddingGenerator->generateEmbeddings($input);

$memoryStore->addDocuments($output);

$vector = $embeddingAdapter->embedText('Welches Tier hat die Wittwe?');
$result = $memoryStore->similaritySearch($vector, 4, ['fileName' => 'schildbuerger.txt']);

foreach ($result as $item) {
    echo $item->getContent() . \PHP_EOL . \PHP_EOL;
}
