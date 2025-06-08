# Generating Embeddings

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides a preview of what will be covered in the complete version.

## Basic Embedding Generation

### Installation
```bash
composer require modelflow-ai/embeddings modelflow-ai/openai-adapter
```

### Simple Generation
```php
use ModelflowAi\Embeddings\EmbeddingsRequestHandler;
use ModelflowAi\Embeddings\Handler\EmbeddingsStoreHandler;
use ModelflowAi\Embeddings\Handler\EmbeddingsSimilarityHandler;
use ModelflowAi\Embeddings\Generator\EmbeddingGenerator;
use ModelflowAi\Embeddings\Splitter\EmbeddingSplitter;
use ModelflowAi\Embeddings\Formatter\EmbeddingFormatter;
use ModelflowAi\OpenaiAdapter\Embeddings\OpenaiEmbeddingAdapter;

// Setup adapter
$adapter = new OpenaiEmbeddingAdapter(
    OpenAI::client($_ENV['OPENAI_API_KEY']), 
    'text-embedding-3-small'
);

// Generate embedding for single text
$embedding = $adapter->embedText('Hello, world!');
echo "Generated embedding with " . count($embedding->getVector()) . " dimensions";
```

### Batch Generation
```php
$texts = [
    'PHP is a programming language',
    'Python is used for data science',
    'JavaScript runs in browsers',
    'Java is object-oriented'
];

$embeddings = [];
foreach ($texts as $text) {
    $embeddings[] = $adapter->embedText($text);
}

echo "Generated " . count($embeddings) . " embeddings";
```

## Text Splitting

### Automatic Splitting
```php
$longText = file_get_contents('document.txt');

$splitter = new EmbeddingSplitter(1000); // Max 1000 characters per chunk
$chunks = $splitter->split($longText);

echo "Split into " . count($chunks) . " chunks";

foreach ($chunks as $chunk) {
    $embedding = $adapter->embedText($chunk);
    // Store embedding...
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
$embedding = $smallAdapter->embedText('Sample text');
```

### Mistral Models
```php
use ModelflowAi\MistralAdapter\Embeddings\MistralEmbeddingAdapter;

$mistralAdapter = new MistralEmbeddingAdapter(
    Mistral::client($_ENV['MISTRAL_API_KEY']),
    'mistral-embed'
);

$embedding = $mistralAdapter->embedText('Text to embed');
```

### Local Models (Ollama)
```php
use ModelflowAi\OllamaAdapter\Embeddings\OllamaEmbeddingAdapter;

$ollamaAdapter = new OllamaEmbeddingAdapter(
    Ollama::client('http://localhost:11434'),
    'mxbai-embed-large'
);

$embedding = $ollamaAdapter->embedText('Local embedding generation');
```

## Advanced Generation

### Embedding Pipeline
```php
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
        
        $embeddings = [];
        foreach ($chunks as $index => $chunk) {
            // Generate embedding
            $embedding = $this->adapter->embedText($chunk);
            
            // Add metadata
            $embedding = $this->formatter->format($embedding, array_merge($metadata, [
                'chunk_index' => $index,
                'chunk_size' => strlen($chunk),
                'content_preview' => substr($chunk, 0, 100)
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

// First call generates and caches
$embedding1 = $cachedAdapter->embedText('This text will be cached');

// Second call returns from cache
$embedding2 = $cachedAdapter->embedText('This text will be cached');
```

## Performance Optimization

### Batch Processing
```php
class BatchEmbeddingProcessor
{
    public function __construct(
        private EmbeddingAdapterInterface $adapter,
        private int $batchSize = 100
    ) {}
    
    public function processBatch(array $texts): array
    {
        $embeddings = [];
        $batches = array_chunk($texts, $this->batchSize);
        
        foreach ($batches as $batch) {
            foreach ($batch as $text) {
                $embeddings[] = $this->adapter->embedText($text);
            }
            
            // Small delay to respect rate limits
            usleep(100000); // 0.1 seconds
        }
        
        return $embeddings;
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