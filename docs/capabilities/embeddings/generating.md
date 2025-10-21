# Generating Embeddings

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides a preview of what will be covered in the complete version.

## Basic Embedding Generation

### Installation
```bash
composer require modelflow-ai/embeddings modelflow-ai/openai-adapter
```

### Simple Generation
```php
use ModelflowAi\Embeddings\Adapter\Request\EmbedRequest;
use ModelflowAi\OpenaiAdapter\Embeddings\OpenaiEmbeddingAdapter;

// Setup adapter
$adapter = new OpenaiEmbeddingAdapter(
    OpenAI::client($_ENV['OPENAI_API_KEY']),
    'text-embedding-3-small'
);

// Generate embedding for single text
$request = new EmbedRequest(['Hello, world!']);
$response = $adapter->embed($request);
$vectors = $response->getVectors();

echo "Generated embedding with " . count($vectors[0]) . " dimensions";
```

### Batch Generation
```php
$texts = [
    'PHP is a programming language',
    'Python is used for data science',
    'JavaScript runs in browsers',
    'Java is object-oriented'
];

// Generate embeddings in a single batch request (more efficient)
$request = new EmbedRequest($texts);
$response = $adapter->embed($request);
$vectors = $response->getVectors();

echo "Generated " . count($vectors) . " embeddings";
echo "Used " . $response->getUsage()->getTotalTokens() . " tokens";
```

## Text Splitting

### Automatic Splitting
```php
$longText = file_get_contents('document.txt');

$splitter = new EmbeddingSplitter(1000); // Max 1000 characters per chunk
$chunks = $splitter->split($longText);

echo "Split into " . count($chunks) . " chunks";

// Process chunks in batches for better performance
$request = new EmbedRequest($chunks);
$response = $adapter->embed($request);
$vectors = $response->getVectors();

// Store embeddings...
foreach ($vectors as $index => $vector) {
    echo "Chunk $index has " . count($vector) . " dimensions\n";
}
```

### Custom Splitting
```php
class CustomSplitter
{
    public function splitByParagraphs(string $text): array
    {
        return array_filter(explode("\n\n", $text));
    }
    
    public function splitBySentences(string $text): array
    {
        return array_filter(explode('.', $text));
    }
    
    public function splitByWords(string $text, int $wordsPerChunk = 100): array
    {
        $words = explode(' ', $text);
        return array_chunk($words, $wordsPerChunk);
    }
}
```

## Model Selection

### OpenAI Models
```php
// Different OpenAI embedding models
$smallAdapter = new OpenaiEmbeddingAdapter($client, 'text-embedding-3-small'); // 1536 dimensions
$largeAdapter = new OpenaiEmbeddingAdapter($client, 'text-embedding-3-large'); // 3072 dimensions
$adaAdapter = new OpenaiEmbeddingAdapter($client, 'text-embedding-ada-002'); // 1536 dimensions

// Use small for general purposes, large for higher quality
$request = new EmbedRequest(['Sample text']);
$response = $smallAdapter->embed($request);
$vector = $response->getVectors()[0];
```

### Mistral Models
```php
use ModelflowAi\MistralAdapter\Embeddings\MistralEmbeddingAdapter;

$mistralAdapter = new MistralEmbeddingAdapter(
    Mistral::client($_ENV['MISTRAL_API_KEY']),
    'mistral-embed'
);

$request = new EmbedRequest(['Text to embed']);
$response = $mistralAdapter->embed($request);
$vector = $response->getVectors()[0];
```

### Local Models (Ollama)
```php
use ModelflowAi\OllamaAdapter\Embeddings\OllamaEmbeddingAdapter;

$ollamaAdapter = new OllamaEmbeddingAdapter(
    Ollama::client('http://localhost:11434'),
    'mxbai-embed-large'
);

$request = new EmbedRequest(['Local embedding generation']);
$response = $ollamaAdapter->embed($request);
$vector = $response->getVectors()[0];
```

## Advanced Generation

### Embedding Pipeline
```php
use ModelflowAi\Embeddings\Adapter\Request\EmbedRequest;

class EmbeddingPipeline
{
    public function __construct(
        private EmbeddingAdapterInterface $adapter,
        private EmbeddingSplitter $splitter,
        private EmbeddingFormatter $formatter
    ) {}

    public function processDocument(string $content, array $metadata = []): array
    {
        // Split content
        $chunks = $this->splitter->split($content);

        // Generate all embeddings in a single batch request (efficient!)
        $request = new EmbedRequest($chunks);
        $response = $this->adapter->embed($request);
        $vectors = $response->getVectors();

        // Format embeddings with metadata
        $embeddings = [];
        foreach ($vectors as $index => $vector) {
            $embedding = $this->formatter->format($vector, array_merge($metadata, [
                'chunk_index' => $index,
                'chunk_size' => strlen($chunks[$index]),
                'content' => $chunks[$index],
                'content_preview' => substr($chunks[$index], 0, 100)
            ]));

            $embeddings[] = $embedding;
        }

        return $embeddings;
    }
}

// Usage
$pipeline = new EmbeddingPipeline($adapter, $splitter, $formatter);
$embeddings = $pipeline->processDocument($documentContent, [
    'document_id' => 'doc_123',
    'title' => 'API Documentation',
    'author' => 'John Doe'
]);
```

### Caching Embeddings
```php
use ModelflowAi\Embeddings\Adapter\Cache\CacheEmbeddingAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

$cache = new FilesystemAdapter('embeddings', 3600); // 1 hour TTL
$cachedAdapter = new CacheEmbeddingAdapter($originalAdapter, $cache);

// First call generates and caches each text individually
$request1 = new EmbedRequest(['This text will be cached', 'Another text']);
$response1 = $cachedAdapter->embed($request1);

// Second call with same texts returns from cache (very fast!)
// Mixed requests only generate embeddings for uncached texts
$request2 = new EmbedRequest(['This text will be cached', 'New uncached text']);
$response2 = $cachedAdapter->embed($request2); // Only 'New uncached text' is generated
```

## Performance Optimization

### Why Batch Processing Matters

The new batch API significantly improves performance:

- **Reduced API Calls**: Generate 100 embeddings in 1 call instead of 100 calls
- **Lower Latency**: Network overhead reduced by ~99% for large batches
- **Better Throughput**: Process thousands of texts in seconds, not minutes
- **Cost Efficiency**: Some providers offer better rates for batch requests
- **Automatic Caching**: CacheEmbeddingAdapter intelligently caches at the individual text level

**Example performance comparison:**
```php
// OLD WAY (deprecated): 100 API calls for 100 texts
foreach ($texts as $text) {
    $vector = $adapter->embedText($text); // Don't do this!
}

// NEW WAY: 1 API call for 100 texts
$request = new EmbedRequest($texts);
$response = $adapter->embed($request);
$vectors = $response->getVectors(); // Much faster!
```

### Batch Processing
```php
use ModelflowAi\Embeddings\Adapter\Request\EmbedRequest;

class BatchEmbeddingProcessor
{
    public function __construct(
        private EmbeddingAdapterInterface $adapter,
        private int $batchSize = 100
    ) {}

    public function processBatch(array $texts): array
    {
        $allVectors = [];
        $batches = array_chunk($texts, $this->batchSize);

        foreach ($batches as $batch) {
            // Process entire batch in single API call (much more efficient!)
            $request = new EmbedRequest($batch);
            $response = $this->adapter->embed($request);
            $allVectors = array_merge($allVectors, $response->getVectors());

            // Small delay to respect rate limits
            usleep(100000); // 0.1 seconds
        }

        return $allVectors;
    }
}
```

### Parallel Processing
```php
class ParallelEmbeddingProcessor
{
    public function processParallel(array $texts, int $workers = 4): array
    {
        $chunks = array_chunk($texts, ceil(count($texts) / $workers));
        $processes = [];
        
        foreach ($chunks as $chunk) {
            $process = new Process(['php', 'embed_worker.php', json_encode($chunk)]);
            $process->start();
            $processes[] = $process;
        }
        
        $embeddings = [];
        foreach ($processes as $process) {
            $process->wait();
            $result = json_decode($process->getOutput(), true);
            $embeddings = array_merge($embeddings, $result);
        }
        
        return $embeddings;
    }
}
```

## Quality Considerations

### Text Preprocessing
```php
class TextPreprocessor
{
    public function clean(string $text): string
    {
        // Remove extra whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Remove special characters
        $text = preg_replace('/[^\w\s\-.,!?]/', '', $text);
        
        // Trim
        return trim($text);
    }
    
    public function normalizeLength(string $text, int $maxLength = 1000): string
    {
        if (strlen($text) <= $maxLength) {
            return $text;
        }
        
        // Truncate at sentence boundary
        $truncated = substr($text, 0, $maxLength);
        $lastPeriod = strrpos($truncated, '.');
        
        return $lastPeriod ? substr($truncated, 0, $lastPeriod + 1) : $truncated;
    }
}
```

*Complete documentation coming soon...*