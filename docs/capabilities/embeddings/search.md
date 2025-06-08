# Search

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides a preview of what will be covered in the complete version.

## Similarity Search

### Basic Search
```php
// Search for similar content
$results = $embeddingsHandler->createSimilarityRequest('machine learning algorithms', 'default')
    ->withLimit(5)
    ->execute();

foreach ($results as $result) {
    echo "Content: " . $result->getContent() . "\n";
    echo "Score: " . $result->getDistance() . "\n\n";
}
```

### Advanced Search
```php
$results = $embeddingsHandler->createSimilarityRequest('AI programming', 'default')
    ->withLimit(10)
    ->withFilter(['category' => 'programming'])
    ->withThreshold(0.8)
    ->execute();
```

## Search Implementation

### Search Service
```php
class EmbeddingSearchService
{
    public function __construct(
        private EmbeddingsRequestHandlerInterface $handler,
        private EmbeddingAdapterInterface $adapter
    ) {}
    
    public function search(string $query, array $options = []): array
    {
        $results = $this->handler->createSimilarityRequest($query, 'default')
            ->withLimit($options['limit'] ?? 10)
            ->execute();
            
        return array_map(function($result) {
            return [
                'content' => $result->getContent(),
                'score' => $result->getDistance(),
                'metadata' => $result->getMetadata()
            ];
        }, $results);
    }
    
    public function semanticSearch(string $query, array $filters = []): array
    {
        return $this->handler->createSimilarityRequest($query, 'default')
            ->withFilter($filters)
            ->withLimit(20)
            ->execute();
    }
}
```

*Complete documentation coming soon...*