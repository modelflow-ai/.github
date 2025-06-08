# Performance

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides a preview of what will be covered in the complete version.

## Optimization Strategies

### Connection Pooling
```php
// Configure HTTP client with connection pooling
$client = HttpClient::create([
    'max_host_connections' => 10,
    'timeout' => 30
]);
```

### Caching
```php
use ModelflowAi\Embeddings\Adapter\Cache\CacheEmbeddingAdapter;

$cache = new FilesystemAdapter('embeddings', 3600);
$cachedAdapter = new CacheEmbeddingAdapter($originalAdapter, $cache);
```

### Streaming
```php
// Use streaming for better perceived performance
$response = $chatHandler->createStreamedRequest()
    ->addUserMessage('Long response expected')
    ->execute();

foreach ($response->getMessageStream() as $chunk) {
    echo $chunk->content;
    flush();
}
```

### Batch Processing
```php
// Process multiple embeddings in batches
$batches = array_chunk($texts, 100);
foreach ($batches as $batch) {
    foreach ($batch as $text) {
        $embeddings[] = $adapter->embedText($text);
    }
    usleep(100000); // Rate limiting
}
```

*Complete documentation coming soon...*