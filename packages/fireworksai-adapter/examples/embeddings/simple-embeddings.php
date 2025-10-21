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

use ModelflowAi\Embeddings\Adapter\Request\EmbedRequest;
use ModelflowAi\FireworksAiAdapter\Embeddings\FireworksAiEmbeddingAdapter;

$fireworksAiClient = require_once \dirname(__DIR__) . '/bootstrap.php';

echo "=== Simple FireworksAI Embeddings Example ===\n\n";

// Initialize embedding adapter
$embeddingAdapter = new FireworksAiEmbeddingAdapter($fireworksAiClient);

// Sample texts to embed
$texts = [
    'The quick brown fox jumps over the lazy dog.',
    'Artificial intelligence is revolutionizing technology.',
    'Climate change requires urgent global action.',
];

echo "Generating embeddings for text samples...\n\n";

foreach ($texts as $index => $text) {
    $displayIndex = $index + 1;
    echo "Text {$displayIndex}: {$text}\n";

    // Create embed request
    $request = new EmbedRequest([$text]);

    // Get embedding
    $response = $embeddingAdapter->embed($request);

    // Display results
    $embedding = $response->getVectors()[0];
    $usage = $response->getUsage();

    echo '   📊 Embedding dimensions: ' . \count($embedding) . "\n";
    echo '   📈 First 5 values: [' . \implode(', ', \array_map('number_format', \array_slice($embedding, 0, 5), \array_fill(0, 5, 4))) . "]\n";
    echo "   🔢 Prompt tokens: {$usage->getPromptTokens()}\n";
    echo "   🔢 Total tokens: {$usage->getTotalTokens()}\n\n";
}

// Demonstrate basic similarity calculation
echo "Calculating cosine similarity between first two embeddings...\n";

$request1 = new EmbedRequest([$texts[0]]);
$request2 = new EmbedRequest([$texts[1]]);

$response1 = $embeddingAdapter->embed($request1);
$response2 = $embeddingAdapter->embed($request2);

$embedding1 = $response1->getVectors()[0];
$embedding2 = $response2->getVectors()[0];

// Simple cosine similarity calculation
$dotProduct = 0;
$magnitude1 = 0;
$magnitude2 = 0;
$counter = \count($embedding1);

for ($i = 0; $i < $counter; ++$i) {
    $dotProduct += $embedding1[$i] * $embedding2[$i];
    $magnitude1 += $embedding1[$i] ** 2;
    $magnitude2 += $embedding2[$i] ** 2;
}

$magnitude1 = \sqrt($magnitude1);
$magnitude2 = \sqrt($magnitude2);

$similarity = $dotProduct / ($magnitude1 * $magnitude2);

echo '📊 Cosine similarity: ' . \number_format($similarity, 4) . "\n\n";

echo "=== Example completed successfully! ===\n";
