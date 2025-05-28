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

require_once \dirname(__DIR__) . '/vendor/autoload.php';

use ModelflowAi\Anthropic\Anthropic;
use ModelflowAi\AnthropicAdapter\Embeddings\AnthropicEmbeddingAdapter;
use ModelflowAi\Embeddings\Adapter\Request\EmbedRequest;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(__DIR__ . '/.env');

$anthropicApiKey = $_ENV['ANTHROPIC_API_KEY'];
if (!$anthropicApiKey) {
    throw new \RuntimeException('Anthropic API key is required');
}

$voyageApiKey = $_ENV['VOYAGE_API_KEY'];
if (!$voyageApiKey) {
    throw new \RuntimeException('Voyage API key is required for embeddings');
}

$anthropicClient = Anthropic::client($anthropicApiKey);
$embeddingAdapter = new AnthropicEmbeddingAdapter($anthropicClient, 'voyage-3');

$text = 'Hello world! This is a test document for embeddings using the ModelflowAI framework.';
$request = new EmbedRequest($text);

$response = $embeddingAdapter->embed($request);

echo 'Text: ' . $text . \PHP_EOL;
echo 'Embedding dimensions: ' . \count($response->getVector()) . \PHP_EOL;
echo 'First 5 embedding values: ' . \implode(', ', \array_slice($response->getVector(), 0, 5)) . \PHP_EOL;
echo 'Prompt tokens: ' . $response->getUsage()->getPromptTokens() . \PHP_EOL;
echo 'Total tokens: ' . $response->getUsage()->getTotalTokens() . \PHP_EOL;