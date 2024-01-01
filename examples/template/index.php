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
use ModelflowAi\Embeddings\Splitter\EmbeddingSplitter;
use ModelflowAi\Embeddings\Generator\EmbeddingGenerator;
use ModelflowAi\Embeddings\Store\Memory\MemoryEmbeddingsStore;
use ModelflowAi\Ollama\Embeddings\OllamaEmbeddingAdapter;
use ModelflowAi\PromptTemplate\PromptTemplate;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpClient\HttpClient;

require_once __DIR__ . '/vendor/autoload.php';

$promptTemplate = new PromptTemplate(
    <<<'PROMPT'
Human: What is the capital of {place}?
AI: The capital of {place} is {capital}
PROMPT
);

echo $promptTemplate->format(['place' => 'Germany', 'capital' => 'Berlin']) . "\n";
