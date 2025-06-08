# Embeddings

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides a preview of what will be covered in the complete version.

## Embeddings Overview

Vector embeddings convert text into numerical vectors that capture semantic meaning, enabling powerful search, similarity, and recommendation systems.

**What are embeddings:**
- Numerical representations of text that preserve semantic meaning
- Enable similarity comparisons between pieces of text
- Foundation for RAG (Retrieval-Augmented Generation) systems
- Power semantic search and recommendation engines

**Use cases:**
- Semantic search engines
- Document similarity and clustering
- Retrieval-Augmented Generation (RAG)
- Content recommendation systems
- Duplicate content detection

## Core Features

- **🔢 Text to Vector** - Convert text into numerical vectors
- **🔍 Semantic Similarity** - Find related content based on meaning
- **⚡ Batch Processing** - Handle multiple texts efficiently
- **💾 Storage Integration** - Connect to vector databases (Qdrant, Elasticsearch)
- **🔄 Provider Agnostic** - Works with multiple embedding providers

## Quick Start

### Installation
```bash
composer require modelflow-ai/embeddings modelflow-ai/openai-adapter
# Optional: modelflow-ai/qdrant-embeddings-store
```

### Basic Usage
```php
use ModelflowAi\Embeddings\EmbeddingsRequestHandler;
use ModelflowAi\Embeddings\Model\Embedding;
use ModelflowAi\Embeddings\Handler\EmbeddingsStoreHandler;
use ModelflowAi\Embeddings\Handler\EmbeddingsSimilarityHandler;
use ModelflowAi\Embeddings\Generator\EmbeddingGenerator;
use ModelflowAi\Embeddings\Splitter\EmbeddingSplitter;
use ModelflowAi\Embeddings\Formatter\EmbeddingFormatter;
use ModelflowAi\Embeddings\Store\Filesystem\FilesystemEmbeddingsStore;
use ModelflowAi\OpenaiAdapter\Embeddings\OpenaiEmbeddingAdapter;

// Setup
$adapter = new OpenaiEmbeddingAdapter(
    OpenAI::client($_ENV['OPENAI_API_KEY']), 
    'text-embedding-3-small'
);
$store = new FilesystemEmbeddingsStore('/tmp/embeddings.txt');

$generator = new EmbeddingGenerator(
    new EmbeddingSplitter(500),
    new EmbeddingFormatter()
);

$storeHandler = new EmbeddingsStoreHandler(
    ['default' => $generator],
    ['default' => $store],
    ['default' => $adapter],
    [Embedding::class => 'default']
);

$similarityHandler = new EmbeddingsSimilarityHandler(
    ['default' => $store],
    ['default' => $adapter]
);

$embeddingsHandler = new EmbeddingsRequestHandler($storeHandler, $similarityHandler);

// Store documents
$embeddingsHandler->createStoreRequest()
    ->addContent('PHP is a popular web development language')
    ->addContent('Python is great for data science and AI')
    ->withKey('default')
    ->execute();

// Search for similar content
$results = $embeddingsHandler->createSimilarityRequest('web programming languages', 'default')
    ->withLimit(5)
    ->execute();

foreach ($results as $result) {
    echo "Match: " . $result->getContent() . " (score: " . $result->getDistance() . ")\n";
}
```

## Supported Providers

| Provider | Embeddings | Models | Local |
|----------|------------|--------|-------|
| **OpenAI** | ✅ | text-embedding-3-small/large, ada-002 | ❌ |
| **Mistral AI** | ✅ | mistral-embed | ❌ |
| **Fireworks.ai** | ✅ | Various embedding models | ❌ |
| **Ollama** | ✅ | Local embedding models | ✅ |

### 5. Storage Backends
- **Qdrant** - Vector database
- **Elasticsearch** - Search with vectors
- **Simple Arrays** - In-memory storage
- **Custom Storage** - Build your own

### 6. Use Cases
- **Semantic Search** - Find similar documents
- **RAG Systems** - Retrieval-augmented generation
- **Recommendations** - Content similarity
- **Clustering** - Group similar content
- **Duplicate Detection** - Find near-duplicates

## Subpages to Create

- **generating.md** - Creating embeddings from text
- **storage.md** - Vector storage solutions
- **search.md** - Similarity search implementation
- **rag.md** - Retrieval-augmented generation systems