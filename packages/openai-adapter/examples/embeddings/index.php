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
use ModelflowAi\OpenaiAdapter\Embeddings\OpenaiEmbeddingAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

$openaiClient = require_once \dirname(__DIR__) . '/bootstrap.php';
require_once __DIR__ . '/ExampleEmbedding.php';

$embeddingSplitter = new EmbeddingSplitter(500);
$embeddingFormatter = new EmbeddingFormatter();
$embeddingAdapter = new CacheEmbeddingAdapter(
    new OpenaiEmbeddingAdapter($openaiClient),
    new FilesystemAdapter('openai', 0, __DIR__ . '/var/cache'),
);
$embeddingGenerator = new EmbeddingGenerator($embeddingSplitter, $embeddingFormatter);

// Use memory store for this example
$store = new MemoryEmbeddingsStore();

$embeddingClass = ExampleEmbedding::class;
$embeddingKey = 'openai-example-store';

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

// Sample data to embed
$documents = [
    'Artificial Intelligence (AI) is transforming the way we work, learn, and interact with technology. Machine learning algorithms can analyze vast amounts of data to identify patterns and make predictions.',
    'Climate change is one of the most pressing challenges of our time. Rising global temperatures are causing melting ice caps, rising sea levels, and more frequent extreme weather events.',
    'The human brain contains approximately 86 billion neurons, each connected to thousands of other neurons through synapses. This complex network enables consciousness, memory, and thought.',
    'Renewable energy sources like solar and wind power are becoming increasingly cost-effective alternatives to fossil fuels. Solar panel efficiency has improved dramatically over the past decade.',
    'The internet has revolutionized communication, allowing people to connect instantly across vast distances. Social media platforms have changed how we share information and maintain relationships.',
];

// Create embedding objects
$embeddings = [];
foreach ($documents as $index => $content) {
    $embeddings[] = new ExampleEmbedding($content, "document_{$index}.txt", $index < 2 ? 'technology' : 'science');
}

$storeResponse = $embeddingsRequestHandler
    ->createStoreRequest(...$embeddings)
    ->execute();

echo "=== OpenAI Embeddings Example ===\n\n";

// Store embeddings
echo "1. Storing embeddings...\n";
echo "   Store Response Usage: {$storeResponse->getUsage()->getPromptTokens()} prompt tokens / " .
     "{$storeResponse->getUsage()->getTotalTokens()} total tokens\n\n";

// Perform similarity searches
$queries = [
    'machine learning and data analysis',
    'global warming and environmental issues',
    'brain and neural networks',
    'solar energy and renewable power',
];

foreach ($queries as $query) {
    echo "2. Searching for: \"$query\"\n";
    
    $similarityResponse = $embeddingsRequestHandler
        ->createSimilarityRequest($query, $embeddingKey)
        ->withLimit(2) // Get top 2 most similar results
        ->execute();

    $count = \count($similarityResponse->getEmbeddings());

    echo "   Similarity Search Usage: {$similarityResponse->getUsage()->getPromptTokens()} prompt tokens\n";
    echo "   Found {$count} similar documents:\n\n";

    foreach ($similarityResponse->getEmbeddings() as $item) {
        echo "   📄 Document: {$item->getFileName()}\n";
        echo "   📝 Content: " . \substr($item->getContent(), 0, 100) . "...\n";
        echo "\n";
    }
    
    echo "   " . \str_repeat('-', 80) . "\n\n";
}

// Demonstrate filtering by category
echo "3. Searching with category filter (technology documents only):\n";
$filteredResponse = $embeddingsRequestHandler
    ->createSimilarityRequest('artificial intelligence and machine learning', $embeddingKey)
    ->withLimit(3)
    ->withAdditionalFilter(['category' => 'technology'])
    ->execute();

$count = \count($filteredResponse->getEmbeddings());
echo "   Found {$count} documents in 'technology' category:\n\n";

foreach ($filteredResponse->getEmbeddings() as $item) {
    echo "   📄 Document: {$item->getFileName()}\n";
    echo "   📝 Content: " . \substr($item->getContent(), 0, 100) . "...\n";
}

echo "=== Example completed successfully! ===\n";