# Standalone Integration

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides a preview of what will be covered in the complete version.

## Overview

Use Modelflow AI without any framework dependencies. Perfect for standalone scripts, custom applications, or non-framework PHP projects.

## Basic Setup

### Installation
```bash
composer require modelflow-ai/chat modelflow-ai/openai-adapter
# Add other adapters as needed
```

### Manual Configuration
```php
use ModelflowAi\Chat\AIChatRequestHandler;
use ModelflowAi\OpenaiAdapter\Chat\OpenaiChatAdapter;
use ModelflowAi\DecisionTree\DecisionTree;
use ModelflowAi\DecisionTree\DecisionRule;
use ModelflowAi\DecisionTree\Criteria\CapabilityCriteria;

// Create provider clients
$openaiClient = OpenAI::client($_ENV['OPENAI_API_KEY']);
$anthropicClient = Anthropic::client($_ENV['ANTHROPIC_API_KEY']);

// Create adapters
$gpt4Adapter = new OpenaiChatAdapter($openaiClient, 'gpt-4o');
$claudeAdapter = new AnthropicChatAdapter($anthropicClient, 'claude-3-5-sonnet');

// Set up decision tree
$decisionTree = new DecisionTree([
    new DecisionRule($gpt4Adapter, [CapabilityCriteria::ADVANCED]),
    new DecisionRule($claudeAdapter, [CapabilityCriteria::BASIC])
]);

// Create request handler
$chatHandler = new AIChatRequestHandler($decisionTree);

// Use it
$response = $chatHandler->createRequest()
    ->addUserMessage('Hello!')
    ->execute();

echo $response->getMessage()->content;
```

## Multi-Capability Setup

### Complete Example
```php
// Chat setup
$chatHandler = new AIChatRequestHandler($chatDecisionTree);

// Completion setup
$completionHandler = new AICompletionRequestHandler($completionDecisionTree);

// Embeddings setup
$embeddingsHandler = new EmbeddingsRequestHandler($storeHandler, $similarityHandler);

// Image setup
$imageHandler = new AIImageRequestHandler($imageMiddleware);
```

## Environment Configuration

### .env File
```bash
OPENAI_API_KEY=sk-...
ANTHROPIC_API_KEY=sk-ant-...
OLLAMA_URL=http://localhost:11434
MISTRAL_API_KEY=...
```

### Loading Environment Variables
```php
// Using vlucas/phpdotenv
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Or manually
$_ENV['OPENAI_API_KEY'] = 'your-key-here';
```

*Complete documentation coming soon...*