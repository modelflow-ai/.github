# Embeddings

> **Placeholder Content** - This should be the comprehensive Embeddings capability documentation.

## What Should Be Here

### 1. Embeddings Overview
- What are vector embeddings
- How they capture semantic meaning
- Why they're useful for AI applications

### 2. Core Features
- **Text to Vector** - Convert text into numerical vectors
- **Semantic Similarity** - Find related content
- **Batch Processing** - Handle multiple texts efficiently
- **Storage Integration** - Connect to vector databases
- **Search & Retrieval** - Build search systems

### 3. Quick Start
```php
// Simple embeddings example with correct API
use ModelflowAi\Embeddings\AIEmbeddingsRequestHandlerInterface;

$response = $embeddingsHandler->createRequest()
    ->addText('Modelflow AI is awesome')
    ->execute();

$vector = $response->getEmbeddings()[0]->getVector();
echo "Generated " . count($vector) . " dimensions";
```

### 4. Supported Providers
- **OpenAI** - text-embedding-ada-002, text-embedding-3-small/large
- **Mistral AI** - mistral-embed
- **Fireworks.ai** - various embedding models
- **Ollama** - local embedding models

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