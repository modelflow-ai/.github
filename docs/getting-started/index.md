# Getting Started

Modelflow AI is a unified PHP library that provides a consistent interface for working with multiple AI providers. Instead of learning different APIs for each provider, you use one simple, elegant interface that works with Anthropic, OpenAI, Ollama, Google Gemini, Mistral AI, and Fireworks.ai.

## Why Modelflow AI?

- **🔄 Provider Agnostic** - Switch between AI providers without changing your code
- **🛡️ Privacy First** - Run models locally with Ollama or choose cloud providers based on your needs
- **⚙️ Smart Routing** - Automatic provider selection based on your criteria
- **🏗️ Enterprise Ready** - Built with Symfony's dependency injection and strict PHP typing
- **📦 Modular** - Install only the capabilities you need

## Four Core Capabilities

Modelflow AI provides four main capabilities that cover all your AI needs:

### 🗨️ Chat

Build conversational AI applications with multi-turn conversations, streaming responses, and function calling.

```php
use ModelflowAi\Chat\AIChatRequestHandlerInterface;

$response = $chatHandler->createRequest()
    ->addSystemMessage('You are a helpful assistant.')
    ->addUserMessage('Explain machine learning in simple terms')
    ->execute();

echo $response->getMessage()->getContent();
```

**Supported by:** OpenAI, Anthropic, Ollama, Google Gemini, Mistral AI, Fireworks.ai  
**Features:** Streaming, Function calling, Multi-modal (text + images)

[→ Learn more about Chat](/capabilities/chat/)

### 📝 Completion

Generate and complete text for creative writing, code generation, and single-turn tasks.

```php
use ModelflowAi\Completion\AICompletionRequestHandlerInterface;

$response = $completionHandler->createRequest('Write a function that calculates fibonacci numbers:')
    ->build()
    ->execute();

echo $response->getContent();
```

**Supported by:** Fireworks.ai, Ollama  
**Features:** Streaming, JSON format, Criteria-based routing

[→ Learn more about Completion](/capabilities/completion/)

### 🧠 Embeddings

Convert text into vector embeddings for semantic search, recommendations, and similarity analysis.

```php
use ModelflowAi\Embeddings\AIEmbeddingsRequestHandlerInterface;

// Store embeddings
$response = $embeddingsHandler->createStoreRequest($embedding1, $embedding2)
    ->execute();

// Search similar content
$response = $embeddingsHandler->createSimilarityRequest('search query', 'default')
    ->withLimit(5)
    ->execute();

foreach ($response->getEmbeddings() as $embedding) {
    echo $embedding->getContent() . "\n";
}
```

**Supported by:** OpenAI, Mistral AI, Fireworks.ai, Ollama  
**Features:** Batch processing, Multiple storage backends, Similarity search

[→ Learn more about Embeddings](/capabilities/embeddings/)

### 🖼️ Image

Generate images from text with support for multiple providers and formats.

```php
use ModelflowAi\Image\AIImageRequestHandlerInterface;
use ModelflowAi\Image\Request\ImageFormat;

$response = $imageHandler->createRequest()
    ->textToImage('A serene mountain landscape at sunset')
    ->imageFormat(ImageFormat::PNG)
    ->asStream()
    ->build()
    ->execute();

$imageStream = $response->stream();
file_put_contents('landscape.png', $imageStream);
```

**Supported by:** OpenAI DALL-E, Fireworks.ai Stable Diffusion  
**Features:** Text-to-image, Stream/Base64 output, PNG/JPEG formats

[→ Learn more about Image](/capabilities/image/)

## Provider Support Matrix

| Provider | Chat | Completion | Embeddings | Images | Local |
|----------|------|------------|------------|--------|-------|
| **OpenAI** | ✅ | ❌ | ✅ | 🚧 | ❌ |
| **Anthropic** | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Ollama** | ✅ | ✅ | ✅ | ❌ | ✅ |
| **Google Gemini** | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Mistral AI** | ✅ | ❌ | ✅ | ❌ | ❌ |
| **Fireworks.ai** | ✅ | ✅ | ✅ | 🚧 | ❌ |

*Legend: ✅ Available, 🚧 Coming Soon, ❌ Not Supported*

## Quick Setup

::: code-group

```bash [💬 Chat]
# Install chat package and a provider
composer require modelflow-ai/chat
composer require modelflow-ai/openai-adapter
```

```bash [📝 Completion]
# Install completion package and a provider
composer require modelflow-ai/completion
composer require modelflow-ai/fireworksai-adapter  # or ollama-adapter
```

```bash [🧠 Embeddings]
# Install embeddings package and a provider
composer require modelflow-ai/embeddings
composer require modelflow-ai/openai-adapter
```

```bash [🖼️ Image]
# Install image package and a provider
composer require modelflow-ai/image
composer require modelflow-ai/openai-adapter  # or fireworksai-adapter
```

```bash [🔌 All Providers]
# Available provider adapters
composer require modelflow-ai/openai-adapter
composer require modelflow-ai/anthropic-adapter
composer require modelflow-ai/ollama-adapter
composer require modelflow-ai/google-gemini-adapter
composer require modelflow-ai/mistral-adapter
composer require modelflow-ai/fireworksai-adapter
```

:::

### Manual Setup Examples

If you prefer to set up Modelflow AI manually without a framework, here are complete examples for each capability:

::: code-group

```php [💬 Chat]
<?php
use ModelflowAi\OpenaiAdapter\Chat\OpenaiChatAdapter;
use ModelflowAi\Chat\AIChatRequestHandler;
use ModelflowAi\DecisionTree\DecisionTree;
use ModelflowAi\DecisionTree\DecisionRule;

// Create OpenAI client and adapter
$client = OpenAI::client($_ENV['OPENAI_API_KEY']);
$adapter = new OpenaiChatAdapter($client, 'gpt-4o');

// Create decision tree and handler
$decisionTree = new DecisionTree([new DecisionRule($adapter)]);
$chatHandler = new AIChatRequestHandler($decisionTree);

// Use it
$response = $chatHandler->createRequest()
    ->addSystemMessage('You are a helpful assistant.')
    ->addUserMessage('Explain quantum computing in simple terms')
    ->execute();

echo $response->getMessage()->content;

```

```php [📝 Completion]
<?php
use ModelflowAi\Completion\AICompletionRequestHandler;
use ModelflowAi\DecisionTree\DecisionTree;
use ModelflowAi\DecisionTree\DecisionRule;
use ModelflowAi\Ollama\Ollama;
use ModelflowAi\OllamaAdapter\Completion\OllamaCompletionAdapter;

// Create Fireworks.ai client and adapter
$client = Ollama::client();
$adapter = new OllamaCompletionAdapter($client, 'llama4');

// Create decision tree and handler
$decisionTree = new DecisionTree([new DecisionRule($adapter)]);
$completionHandler = new AICompletionRequestHandler($decisionTree);

// Use it
$response = $completionHandler->createRequest('Write a PHP function that validates email addresses:')
    ->build()
    ->execute();

echo $response->getContent();
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
use ModelflowAi\Embeddings\Store\Filesystem\FilesystemEmbeddingsStore;
use ModelflowAi\OllamaAdapter\Embeddings\OllamaEmbeddingAdapter;
use ModelflowAi\Ollama\Ollama;

// Setup components
$embeddingSplitter = new EmbeddingSplitter(500);
$embeddingFormatter = new EmbeddingFormatter();
$embeddingAdapter = new OllamaEmbeddingAdapter(Ollama::client(), 'all-minilm');
$embeddingGenerator = new EmbeddingGenerator($embeddingSplitter, $embeddingFormatter);

// Setup store
$store = new FilesystemEmbeddingsStore('/tmp/embeddings.txt');
$embeddingKey = 'simple-store';

// Setup handlers
$storeHandler = new EmbeddingsStoreHandler(
    [$embeddingKey => $embeddingGenerator],
    [$embeddingKey => $store],
    [$embeddingKey => $embeddingAdapter],
    [Embedding::class => $embeddingKey],
);

$similarityHandler = new EmbeddingsSimilarityHandler(
    [$embeddingKey => $store],
    [$embeddingKey => $embeddingAdapter],
);

$embeddingsHandler = new EmbeddingsRequestHandler($storeHandler, $similarityHandler);

// Store embeddings
$embedding1 = new Embedding('Machine learning is a subset of AI', 'doc1');
$embedding2 = new Embedding('Deep learning uses neural networks', 'doc2');
$embedding3 = new Embedding('Reading a book can enhance your intelligence and broaden your knowledge.', 'doc3');

$storeResponse = $embeddingsHandler->createStoreRequest($embedding1, $embedding2, $embedding3)
    ->execute();

// Search similar content
$searchResponse = $embeddingsHandler->createSimilarityRequest('What is AI?', $embeddingKey)
    ->withLimit(2)
    ->execute();

foreach ($searchResponse->getEmbeddings() as $embedding) {
    echo $embedding->getContent() . "\n";
}
```

```php [🖼️ Image]
<?php
use ModelflowAi\OpenaiAdapter\Image\OpenAIImageGenerationAdapter;
use ModelflowAi\Image\AIImageRequestHandler;
use ModelflowAi\DecisionTree\DecisionTree;
use ModelflowAi\DecisionTree\DecisionRule;
use ModelflowAi\Image\Middleware\HandleMiddleware;
use ModelflowAi\Image\Request\Value\ImageFormat;
use Symfony\Component\HttpClient\HttpClient;

// Create OpenAI client and adapter
$openaiClient = OpenAI::client($_ENV['OPENAI_API_KEY']);
$adapter = new OpenAIImageGenerationAdapter($httpClient, $openaiClient, 'dall-e-3');

// Create decision tree and handler
$decisionTree = new DecisionTree([new DecisionRule($adapter)]);
$middleware = new HandleMiddleware($decisionTree);
$imageHandler = new AIImageRequestHandler($middleware);

// Generate image
$response = $imageHandler->createRequest()
    ->textToImage('A futuristic city skyline at sunset with flying cars')
    ->imageFormat(ImageFormat::PNG)
    ->asStream()
    ->build()
    ->execute();

// Save the image
$imageStream = $response->stream();
file_put_contents('futuristic_city.png', $imageStream);
echo "Image saved as futuristic_city.png\n";
```

:::

## Next Steps

- **🚀 [Quick Start](./quick-start)** - Working examples for all capabilities
- **📦 [Installation](./installation)** - Detailed installation and setup guide
- **🏗️ [Architecture](./architecture)** - Understanding how Modelflow AI works
- **💡 [Core Concepts](./concepts)** - Key concepts and patterns

## Choose Your Path

**Want to see code?** Jump to [Quick Start](./quick-start) for copy-pasteable examples.

**Ready to install?** Head to [Installation](./installation) for a step-by-step setup guide.

**Need specific capability?** Explore [Chat](/capabilities/chat/), [Completion](/capabilities/completion/), or [Embeddings](/capabilities/embeddings/).

**Using a framework?** Check out [Symfony Integration](/integration/symfony) for full framework setup.
