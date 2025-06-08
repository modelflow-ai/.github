# Embeddings Search Engine

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides a preview of what will be covered in the complete version.

## Semantic Search Engine

### Search Service
```php
class SemanticSearchEngine
{
    public function __construct(
        private EmbeddingsRequestHandlerInterface $embeddingsHandler
    ) {}
    
    public function search(string $query, array $filters = []): array
    {
        $results = $this->embeddingsHandler
            ->createSimilarityRequest($query, 'search_index')
            ->withLimit(10)
            ->withFilter($filters)
            ->execute();
            
        return array_map(function($result) {
            return [
                'content' => $result->getContent(),
                'score' => $result->getDistance(),
                'metadata' => $result->getMetadata()
            ];
        }, $results);
    }
    
    public function indexContent(string $content, array $metadata): void
    {
        $this->embeddingsHandler->createStoreRequest()
            ->addContent($content)
            ->withMetadata($metadata)
            ->withKey('search_index')
            ->execute();
    }
}
```

### Content Indexing
```php
class ContentIndexer
{
    public function indexWebsite(string $url): void
    {
        $pages = $this->crawlWebsite($url);
        
        foreach ($pages as $page) {
            $this->searchEngine->indexContent($page['content'], [
                'url' => $page['url'],
                'title' => $page['title'],
                'type' => 'webpage',
                'indexed_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    public function indexDocuments(string $directory): void
    {
        $files = glob($directory . '/*.{txt,md,pdf}', GLOB_BRACE);
        
        foreach ($files as $file) {
            $content = $this->extractText($file);
            
            $this->searchEngine->indexContent($content, [
                'filename' => basename($file),
                'path' => $file,
                'type' => pathinfo($file, PATHINFO_EXTENSION),
                'size' => filesize($file)
            ]);
        }
    }
}
```

*Complete documentation coming soon...*