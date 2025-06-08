# Installation

Modelflow AI is a modular PHP framework that allows you to install only the components you need. This guide will help you get started with the installation process.

## System Requirements

Before installing Modelflow AI, ensure your system meets these requirements:

- **PHP 8.2** or higher
- **Composer 2.0** or higher
- Required PHP extensions:
  - `fileinfo` (required by chat and completion packages)
  - `ctype` (required when using Symfony integration)
  - `iconv` (required when using Symfony integration)

## Installation Methods

### 1. Core Package Installation

Start by installing the core functionality you need. Each capability has its own package:

```bash
# For Chat functionality
composer require modelflow-ai/chat

# For Text Completion
composer require modelflow-ai/completion

# For Embeddings
composer require modelflow-ai/embeddings

# For Image Generation
composer require modelflow-ai/image
```

### 2. Provider Adapters

After installing core packages, add the provider adapters for the AI services you want to use:

```bash
# OpenAI (e.g. GPT-4, GPT-3.5, DALL-E)
composer require modelflow-ai/openai-adapter

# Anthropic (e.g. Claude)
composer require modelflow-ai/anthropic-adapter

# Google Gemini
composer require modelflow-ai/google-gemini-adapter

# Mistral AI
composer require modelflow-ai/mistral-adapter

# Ollama (e.g. Local models)
composer require modelflow-ai/ollama-adapter

# Fireworks AI
composer require modelflow-ai/fireworksai-adapter
```

### 3. Optional Components

Enhance your setup with additional components:

#### AI Experts (Pre-configured AI assistants)
```bash
composer require modelflow-ai/experts
```

#### Prompt Templates
```bash
composer require modelflow-ai/prompt-template
```

#### AI Tools
```bash
composer require modelflow-ai/tools
```

#### Embeddings Storage
```bash
# For Elasticsearch
composer require modelflow-ai/elasticsearch-embeddings-store

# For Qdrant
composer require modelflow-ai/qdrant-embeddings-store
```

## Framework Integration

### Symfony Integration

For Symfony applications, use the official bundle:

```bash
composer require modelflow-ai/symfony-bundle
```

After installation, the bundle will be automatically registered. Configure it in `config/packages/modelflow_ai.yaml`:

```yaml
modelflow_ai:
    # Provider configuration
    providers:
        openai:
            enabled: true
            credentials:
                api_key: '%env(OPENAI_API_KEY)%'
        anthropic:
            enabled: true
            credentials:
                api_key: '%env(ANTHROPIC_API_KEY)%'
        mistral:
            enabled: true
            credentials:
                api_key: '%env(MISTRAL_API_KEY)%'
        ollama:
            enabled: true
            url: '%env(OLLAMA_URL)%'
    
    # Model adapters configuration (optional - these are predefined adapters)
    adapters:
        # OpenAI
        gpt4o:
            enabled: true
        dall_e_3:
            enabled: true
        
        # Anthropic
        claude_3_5_sonnet:
            enabled: true
        claude_3_opus:
            enabled: true
        claude_3_haiku:
            enabled: true
        
        # Mistral
        mistral_large:
            enabled: true
        mistral_nemo:
            enabled: true
        mistral_small:
            enabled: true
        
        # Ollama
        llama3_2:
            enabled: true
        llama3:
            enabled: true
        llava:
            enabled: true
    
    # Embeddings configuration (if using embeddings)
    embeddings:
        generators:
            mistral_embeddings:
                enabled: true
                provider: "mistral"
                model: "mistral-embed"
                splitter:
                    max_length: 1500
                    separator: " "
                cache:
                    enabled: true
                    cache_pool: cache.app
        stores:
            document_store:
                enabled: true
                dsn: 'qdrant://127.0.0.1:6333/documents'
            product_store:
                enabled: true
                dsn: 'qdrant://127.0.0.1:6333/products'
        request_handler:
            enabled: true
            mapping:
                'App\Embedding\DocumentEmbedding':
                    key: 'document_store'
                'App\Embedding\ProductEmbedding':
                    key: 'product_store'
                
    # AI Experts configuration (if using experts)
    experts:
        product_analyst:
            name: "Product Analyst"
            description: "Analyzes product data and provides recommendations"
            instructions: "You are a product analyst expert..."
            response_format:
                type: "json_schema"
                schema:
                    type: "object"
                    properties:
                        analysis:
                            type: "string"
                        confidence_score:
                            type: "number"
                            minimum: 0
                            maximum: 100
```

### Standalone Usage

For non-Symfony applications, create request handlers for each capability:

::: code-group

```php [💬 Chat]
<?php
use ModelflowAi\Chat\AIChatRequestHandler;
use ModelflowAi\OpenaiAdapter\Chat\OpenaiChatAdapter;
use ModelflowAi\AnthropicAdapter\Chat\AnthropicChatAdapter;
use ModelflowAi\MistralAdapter\Chat\MistralChatAdapter;
use ModelflowAi\OllamaAdapter\Chat\OllamaChatAdapter;
use ModelflowAi\DecisionTree\DecisionTree;
use ModelflowAi\DecisionTree\DecisionRule;
use ModelflowAi\DecisionTree\Criteria\ProviderCriteria;
use ModelflowAi\DecisionTree\Criteria\ModelCriteria;

// Create OpenAI client and adapter
$openaiClient = OpenAI::client($_ENV['OPENAI_API_KEY']);
$openaiAdapter = new OpenaiChatAdapter($openaiClient, 'gpt-4o');

// Create Anthropic client and adapter
$anthropicClient = Anthropic::client($_ENV['ANTHROPIC_API_KEY']);
$anthropicAdapter = new AnthropicChatAdapter($anthropicClient, 'claude-3-5-sonnet-20241022');

// Create Mistral client and adapter
$mistralClient = Mistral::client($_ENV['MISTRAL_API_KEY']);
$mistralAdapter = new MistralChatAdapter($mistralClient, 'mistral-large-latest');

// Create Ollama adapter
$ollamaClient = Ollama::client();
$ollamaAdapter = new OllamaChatAdapter($ollamaClient, 'llama3.2');

// Create decision tree with criteria
$decisionTree = new DecisionTree([
    new DecisionRule($openaiAdapter, [
        ProviderCriteria::OPENAI,
        ModelCriteria::GPT4O,
    ]),
    new DecisionRule($anthropicAdapter, [
        ProviderCriteria::ANTHROPIC,
        ModelCriteria::CLAUDE_3_5_SONNET,
    ]),
    new DecisionRule($mistralAdapter, [
        ProviderCriteria::MISTRAL,
        ModelCriteria::MISTRAL_LARGE,
    ]),
    new DecisionRule($ollamaAdapter, [
        ProviderCriteria::OLLAMA,
        ModelCriteria::LLAMA3_2,
    ]),
]);
$chatHandler = new AIChatRequestHandler($decisionTree);
```

```php [📝 Completion]
<?php
use ModelflowAi\Completion\AICompletionRequestHandler;
use ModelflowAi\OllamaAdapter\Completion\OllamaCompletionAdapter;
use ModelflowAi\DecisionTree\DecisionTree;
use ModelflowAi\DecisionTree\DecisionRule;
use ModelflowAi\DecisionTree\Criteria\ProviderCriteria;
use ModelflowAi\DecisionTree\Criteria\ModelCriteria;

// Create Ollama adapter
$ollamaClient = Ollama::client();
$ollamaAdapter = new OllamaCompletionAdapter($ollamaClient, 'llama3.2');

// Create decision tree with criteria
$decisionTree = new DecisionTree([
    new DecisionRule($ollamaAdapter, [
        ProviderCriteria::OLLAMA,
        ModelCriteria::LLAMA3_2,
    ]),
]);
$completionHandler = new AICompletionRequestHandler($decisionTree);
```

```php [🧠 Embeddings]
<?php
use ModelflowAi\Embeddings\EmbeddingsRequestHandler;
use ModelflowAi\Embeddings\Handler\EmbeddingsStoreHandler;
use ModelflowAi\Embeddings\Handler\EmbeddingsSimilarityHandler;
use ModelflowAi\Embeddings\Generator\EmbeddingGenerator;
use ModelflowAi\Embeddings\Model\Embedding;
use ModelflowAi\Embeddings\Splitter\EmbeddingSplitter;
use ModelflowAi\Embeddings\Formatter\EmbeddingFormatter;
use ModelflowAi\QdrantEmbeddingsStore\QdrantEmbeddingsStore;
use ModelflowAi\MistralAdapter\Embeddings\MistralEmbeddingAdapter;
use ModelflowAi\Mistral\Mistral;

// Setup components
$embeddingSplitter = new EmbeddingSplitter(500);
$embeddingFormatter = new EmbeddingFormatter();
$embeddingAdapter = new MistralEmbeddingAdapter(Mistral::client($_ENV['MISTRAL_API_KEY']), 'mistral-embed');
$embeddingGenerator = new EmbeddingGenerator($embeddingSplitter, $embeddingFormatter);

// Setup stores - matching the Symfony config
$documentStore = new QdrantEmbeddingsStore('qdrant://127.0.0.1:6333/documents');
$productStore = new QdrantEmbeddingsStore('qdrant://127.0.0.1:6333/products');

// Define embedding types
class DocumentEmbedding extends Embedding {}
class ProductEmbedding extends Embedding {}

// Setup handlers with multiple stores
$storeHandler = new EmbeddingsStoreHandler(
    ['document_store' => $embeddingGenerator, 'product_store' => $embeddingGenerator],
    ['document_store' => $documentStore, 'product_store' => $productStore],
    ['document_store' => $embeddingAdapter, 'product_store' => $embeddingAdapter],
    [DocumentEmbedding::class => 'document_store', ProductEmbedding::class => 'product_store'],
);

$similarityHandler = new EmbeddingsSimilarityHandler(
    ['document_store' => $documentStore, 'product_store' => $productStore],
    ['document_store' => $embeddingAdapter, 'product_store' => $embeddingAdapter],
);

$embeddingsHandler = new EmbeddingsRequestHandler($storeHandler, $similarityHandler);
```

```php [🖼️ Image]
<?php
use ModelflowAi\Image\AIImageRequestHandler;
use ModelflowAi\OpenaiAdapter\Image\OpenAIImageGenerationAdapter;
use ModelflowAi\DecisionTree\DecisionTree;
use ModelflowAi\DecisionTree\DecisionRule;
use ModelflowAi\DecisionTree\Criteria\ProviderCriteria;
use ModelflowAi\DecisionTree\Criteria\ModelCriteria;

// Create OpenAI image adapter
$openaiClient = OpenAI::client($_ENV['OPENAI_API_KEY']);
$openaiAdapter = new OpenAIImageGenerationAdapter(HttpClient::create(), $openaiClient, 'dall-e-3');

// Create decision tree with criteria
$decisionTree = new DecisionTree([
    new DecisionRule($openaiAdapter, [
        ProviderCriteria::OPENAI,
        ModelCriteria::DALL_E_3,
    ]),
]);
$middleware = new HandleMiddleware($decisionTree);
$imageHandler = new AIImageRequestHandler($middleware);
```

:::

## Environment Configuration

### API Keys

#### For Symfony Applications

Store your API keys in `.env` or `.env.local` files (automatically loaded by Symfony):

```bash
# .env or .env.local
OPENAI_API_KEY=sk-...
ANTHROPIC_API_KEY=sk-ant-...
MISTRAL_API_KEY=...
OLLAMA_URL=http://localhost:11434/api/
```

#### For Standalone Applications

You have several options for managing environment variables:

**Option 1: Use symfony/dotenv package**
```bash
composer require symfony/dotenv
```

```php
<?php
require 'vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');

// Now you can use $_ENV['OPENAI_API_KEY']
```

**Option 2: Set system environment variables directly**
```bash
export OPENAI_API_KEY=sk-...
export ANTHROPIC_API_KEY=sk-ant-...
export MISTRAL_API_KEY=...
export OLLAMA_URL=http://localhost:11434/api/
```

**Option 3: Configure directly in your application**
```php
<?php
// ⚠️ EXTREMELY DANGEROUS - Never do this in production!
// API keys will be exposed in your source code and version control
$openaiClient = OpenAI::client('your-actual-api-key-here');
```

> **⚠️ Security Warning**: Hardcoding API keys directly in your code is extremely dangerous and should never be done in production. This exposes your sensitive credentials in source code, version control, and any logs. Always use environment variables or secure secret management systems.

### Local Models (Ollama)

For Ollama, ensure the service is running:

```bash
# Install Ollama (macOS/Linux)
curl -fsSL https://ollama.com/install.sh | sh

# Start Ollama service
ollama serve

# Pull a model
ollama pull llama3.2
```

## Next Steps

- Continue to [Quick Start](quick-start.md) for your first implementation
- Explore [Architecture](architecture.md) to understand the framework design
- Learn about [Core Concepts](concepts.md) for deeper understanding
