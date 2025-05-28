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

use ModelflowAi\Anthropic\Anthropic;
use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

(new Dotenv())->bootEnv(__DIR__ . '/.env');

$client = Anthropic::client($_ENV['ANTHROPIC_API_KEY']);

$response = $client->embeddings()->create([
    'model' => 'voyage-3',
    'input' => 'Hello world! This is a test document for embeddings.',
    'input_type' => 'document',
]);

echo 'Text: ' . 'Hello world! This is a test document for embeddings.' . \PHP_EOL;
echo 'Model: ' . $response->model . \PHP_EOL;
echo 'Embedding dimensions: ' . \count($response->data[0]->embedding) . \PHP_EOL;
echo 'First 5 embedding values: ' . \implode(', ', \array_slice($response->data[0]->embedding, 0, 5)) . \PHP_EOL;
echo 'Total tokens used: ' . $response->usage->totalTokens . \PHP_EOL;