# Completion

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides a preview of what will be covered in the complete version.

## Completion Overview

Text completion is perfect for single-shot text generation tasks where you need to complete or continue a prompt without maintaining conversation context.

**When to use Completion:**
- Single-shot text generation
- Code completion and generation
- Creative writing prompts
- Template filling
- Simple text transformations

**When to use Chat instead:**
- Multi-turn conversations
- Complex reasoning requiring context
- Tool/function calling
- Vision tasks

## Core Features

- **📝 Text Generation** - Complete or continue text from prompts
- **⚡ Streaming** - Real-time text generation
- **🎛️ Parameter Control** - Temperature, max tokens, stop sequences
- **🔄 Provider Agnostic** - Works with multiple AI providers
- **🚀 High Performance** - Optimized for single-turn generation

## Quick Start

### Installation
```bash
composer require modelflow-ai/completion modelflow-ai/ollama-adapter
```

### Basic Usage
```php
use ModelflowAi\Completion\AICompletionRequestHandler;
use ModelflowAi\DecisionTree\DecisionRule;
use ModelflowAi\DecisionTree\DecisionTree;
use ModelflowAi\Ollama\Ollama;
use ModelflowAi\OllamaAdapter\Completion\OllamaCompletionAdapter;

// Setup
$client = Ollama::client();
$adapter = new OllamaCompletionAdapter($client, 'llama3.2');
$completionHandler = new AICompletionRequestHandler(new DecisionTree([new DecisionRule($adapter)]));

// Complete a prompt
$response = $completionHandler->createRequest('The three most important programming principles are:')
    ->build()
    ->execute();

echo $response->getContent();
// Output: 1. Don't Repeat Yourself (DRY) - avoid code duplication
// 2. Single Responsibility Principle - each function should have one purpose
// 3. Keep It Simple, Stupid (KISS) - write clear, maintainable code
```

### With Parameters
```php
$response = $completionHandler->createRequest('Write a haiku about PHP:')
    ->withMaxTokens(50)
    ->withTemperature(0.7)
    ->build()
    ->execute();

echo $response->getContent();
```

## Supported Providers

| Provider | Completion | Streaming | Local |
|----------|------------|-----------|-------|
| **Ollama** | ✅ | ✅ | ✅ |
| **Fireworks.ai** | ✅ | ✅ | ❌ |

*Note: OpenAI, Anthropic, Google Gemini, and Mistral focus on chat capabilities rather than completion.*

### 5. Use Cases
- **Code Generation** - Complete functions and snippets
- **Creative Writing** - Story and content generation
- **Templates** - Fill-in-the-blank scenarios
- **Summarization** - Condense long text
- **Translation** - Language translation tasks

### 6. Configuration
- Model selection strategies
- Parameter tuning (temperature, top-p, etc.)
- Stop sequences and length control

## Subpages to Create

- **basic-usage.md** - Core completion operations
- **prompt-engineering.md** - Crafting effective prompts
- **use-cases.md** - Common completion scenarios