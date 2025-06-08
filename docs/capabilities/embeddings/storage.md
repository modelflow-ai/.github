# Storage

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides a preview of what will be covered in the complete version.

## Storage Backends

### Filesystem Storage
```php
use ModelflowAi\Embeddings\Store\Filesystem\FilesystemEmbeddingsStore;

$store = new FilesystemEmbeddingsStore('/path/to/embeddings.json');

// Store embedding
$store->addDocument($embedding);

// Store multiple embeddings
$store->addDocuments($embeddings);
```

### Qdrant Vector Database
```bash
composer require modelflow-ai/qdrant-embeddings-store
```

```php
use ModelflowAi\QdrantEmbeddingsStore\QdrantEmbeddingsStore;

$store = new QdrantEmbeddingsStore([
    'host' => 'localhost',
    'port' => 6333,
    'collection' => 'documents'
]);

$store->addDocument($embedding);
```

### Elasticsearch Storage
```bash
composer require modelflow-ai/elasticsearch-embeddings-store
```

```php
use ModelflowAi\ElasticsearchEmbeddingsStore\ElasticsearchEmbeddingsStore;

$store = new ElasticsearchEmbeddingsStore([
    'hosts' => ['localhost:9200'],
    'index' => 'embeddings'
]);

$store->addDocument($embedding);
```

## Custom Storage Implementation

### Database Storage
```php
use ModelflowAi\Embeddings\Store\EmbeddingsStoreInterface;
use ModelflowAi\Embeddings\Model\EmbeddingInterface;

class DatabaseEmbeddingsStore implements EmbeddingsStoreInterface
{
    public function __construct(private PDO $pdo) {}
    
    public function addDocument(EmbeddingInterface $embedding): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO embeddings (content, vector, metadata) VALUES (?, ?, ?)'
        );
        
        $stmt->execute([
            $embedding->getContent(),
            json_encode($embedding->getVector()),
            json_encode($embedding->getMetadata())
        ]);
    }
    
    public function addDocuments(array $embeddings): void
    {
        $this->pdo->beginTransaction();
        
        try {
            foreach ($embeddings as $embedding) {
                $this->addDocument($embedding);
            }
            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollback();
            throw $e;
        }
    }
    
    public function similaritySearch(
        array $vector, 
        int $k = 4, 
        array $additionalArguments = []
    ): array {
        // Implement similarity search logic
        // This would typically use vector similarity functions
        // or approximate nearest neighbor algorithms
    }
}
```

*Complete documentation coming soon...*