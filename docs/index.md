---
layout: home

hero:
  name: "Modelflow AI"
  text: "Unified PHP AI Library"
  tagline: "Connect to any AI provider with a single, elegant interface"
  image:
    src: https://avatars.githubusercontent.com/u/152068817?s=768&v=4
    alt: Modelflow AI
  actions:
    - theme: brand
      text: Get Started
      link: /getting-started/
    - theme: alt
      text: View on GitHub
      link: https://github.com/modelflow-ai

features:
  - title: 🚀 Unified Interface
    details: Work with Anthropic, OpenAI, Ollama, Gemini, Mistral, and more using the same PHP API
  - title: ⚡ Real-time Streaming
    details: Stream responses in real-time from any supported AI provider
  - title: 🧠 Smart Routing
    details: Automatic provider selection based on privacy, capabilities, and performance criteria
  - title: 🔧 Extensible Tools
    details: Function calling and tool execution with built-in middleware support
  - title: 📦 Framework Ready
    details: Symfony Bundle included with full dependency injection support
  - title: 🎯 Production Ready
    details: "Battle-tested in production with comprehensive testing and type safety (Note: Under active development - check releases for updates)"
---

## Quick Example

```php
use ModelflowAi\Chat\AIChatRequestHandlerInterface;

// Works with any provider - Anthropic, OpenAI, Ollama, etc.
$response = $chatHandler->createRequest()
    ->addUserMessage('Explain quantum computing in simple terms')
    ->execute();

echo $response->getMessage()->getContent();
```

## Four Core Capabilities

::: code-group

```php [💬 Chat]
// Conversational AI with any provider
$response = $chatHandler->createRequest()
    ->addUserMessage('Hello! How can I help you today?')
    ->execute();
```

```php [📝 Completion]
// Text generation and completion
$response = $completionHandler->createRequest()
    ->setPrompt('The future of AI is')
    ->setMaxTokens(100)
    ->execute();
```

```php [🧠 Embeddings]
// Vector embeddings for search
$response = $embeddingsHandler->createRequest()
    ->addText('Convert this to a vector')
    ->execute();

$vector = $response->getEmbeddings()[0]->getVector();
```

```php [🖼️ Image]
// Generate images from text (Coming Soon)
$response = $imageHandler->createRequest()
    ->setPrompt('A serene mountain landscape')
    ->execute();

$imageUrl = $response->getImages()[0]->getUrl();
```

:::

## Multiple Provider Support

Modelflow AI provides adapters for all major AI providers:

::: code-group

```php [Anthropic Claude]
use ModelflowAi\AnthropicAdapter\Chat\AnthropicChatAdapter;

$adapter = new AnthropicChatAdapter($client, 'claude-3-5-sonnet-20241022');
```

```php [OpenAI GPT]
use ModelflowAi\OpenaiAdapter\Chat\OpenaiChatAdapter;

$adapter = new OpenaiChatAdapter($client, 'gpt-4o');
```

```php [Ollama Local]
use ModelflowAi\OllamaAdapter\Chat\OllamaChatAdapter;

$adapter = new OllamaChatAdapter($client, 'llama4');
```

```php [Google Gemini]
use ModelflowAi\GoogleGeminiAdapter\Chat\GoogleGeminiChatAdapter;

$adapter = new GoogleGeminiChatAdapter($client, 'gemini-1.5-pro');
```

:::

## Why Modelflow AI?

- **🔄 Provider Agnostic**: Switch between AI providers without changing your code
- **🛡️ Privacy First**: Run models locally with Ollama or choose cloud providers based on your privacy requirements
- **⚙️ Smart Configuration**: Decision tree routing automatically selects the best provider for each task
- **🏗️ Enterprise Ready**: Built with Symfony's dependency injection and PHP's strict typing
- **📈 Scalable**: From simple scripts to complex applications

[Get Started →](/getting-started/)
