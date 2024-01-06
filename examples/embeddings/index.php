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
use ModelflowAi\Embeddings\Formatter\EmbeddingFormatter;
use ModelflowAi\Embeddings\Generator\EmbeddingGenerator;
use ModelflowAi\Embeddings\Splitter\EmbeddingSplitter;
use ModelflowAi\Embeddings\Store\Memory\MemoryEmbeddingsStore;
use ModelflowAi\OllamaAdapter\Embeddings\OllamaEmbeddingAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpClient\HttpClient;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/ExampleEmbedding.php';

$httpClient = HttpClient::create();

$embeddingSplitter = new EmbeddingSplitter(500);
$embeddingFormatter = new EmbeddingFormatter();
$embeddingAdapter = new CacheEmbeddingAdapter(
    new OllamaEmbeddingAdapter($httpClient),
    new FilesystemAdapter('ollama', 0, __DIR__ . '/var/cache'),
);
$embeddingGenerator = new EmbeddingGenerator($embeddingSplitter, $embeddingFormatter, $embeddingAdapter);
$memoryStore = new MemoryEmbeddingsStore();

$input = [
    new ExampleEmbedding(\file_get_contents(__DIR__ . '/var/books/schildbuerger.txt') ?: '', 'schildbuerger.txt'),
    new ExampleEmbedding(\file_get_contents(__DIR__ . '/var/books/nibelungenlied.txt') ?: '', 'nibelungenlied.txt'),
];
$output = [];
foreach ($input as $embedding) {
    $output = \array_merge($output, $embeddingGenerator->embed($embedding));
}

$memoryStore->addDocuments($output);

$vector = $embeddingAdapter->embedText('Welches Tier hat die Wittwe?');
$result = $memoryStore->similaritySearch($vector, 4, ['fileName' => 'schildbuerger.txt']);

foreach ($result as $item) {
    echo $item->getContent() . \PHP_EOL . \PHP_EOL;
}
