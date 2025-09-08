# Chat

The Chat capability in Modelflow AI provides a powerful, flexible system for building conversational AI applications. It offers a unified interface for multi-turn conversations across different AI providers, with support for streaming, tool calling, and multimodal interactions.

## When to Use Chat
- **Conversational Applications**: Chatbots, virtual assistants, support systems
- **Multi-turn Interactions**: Conversations requiring context and memory
- **Complex Reasoning**: Tasks requiring step-by-step thinking and analysis
- **Tool Integration**: Scenarios where AI needs to call functions or use external tools
- **Multimodal Tasks**: Combining text and image analysis in conversations
- **Real-time Interactions**: Applications requiring streaming responses

## Architecture

The Chat system follows a clean request-response flow with middleware processing:

```
┌─────────────┐    ┌──────────────────────┐    ┌──────────────────┐
│ User Input  │ →  │ AIChatRequestBuilder │ →  │  AIChatRequest   │
└─────────────┘    └──────────────────────┘    └──────────────────┘
                                                          │
                                                          ▼
                                               ┌──────────────────┐
                                               │ RequestHandler   │
                                               └──────────────────┘
                                                          │
                                                          ▼
                                               ┌──────────────────┐
                                               │ Middleware Stack │
                                               │ (pre-process)    │
                                               └──────────────────┘
                                                          │
                                                          ▼
                                               ┌──────────────────┐
                                               │  Decision Tree   │ → Select Adapter
                                               └──────────────────┘
                                                          │
                                                          ▼
                                               ┌──────────────────┐
                                               │  AI Provider     │
                                               │ (OpenAI, etc.)   │
                                               └──────────────────┘
                                                          │
                                                          ▼
                                               ┌──────────────────┐
                                               │ Middleware Stack │
                                               │ (post-process)   │
                                               └──────────────────┘
                                                          │
                                                          ▼
┌─────────────┐    ┌──────────────────────┐    ┌──────────────────┐
│    User     │ ←  │   AIChatResponse     │ ←  │ Provider Response│
└─────────────┘    └──────────────────────┘    └──────────────────┘
```

### Core Components

- **`AIChatRequestHandler`**: Main entry point for creating and handling chat requests
- **`AIChatRequest`**: Encapsulates conversation messages, options, and metadata
- **`AIChatRequestBuilder`**: Fluent interface for constructing chat requests
- **`AIChatMiddleware`**: Extensible middleware system for request/response processing
- **`AIChatAdapter`**: Provider-specific implementations (OpenAI, Anthropic, etc.)
- **`AIChatResponse`**: Standardized response format across all providers

## Core Features

### 🗨️ Conversational AI
- Multi-turn conversations with full context awareness
- System, user, and assistant message types
- Message history management and context control
- Support for message metadata and custom attributes

### ⚡ Streaming Support
- Real-time response streaming for better UX
- Chunk-by-chunk processing with event handling
- Stream interruption and error recovery
- Token usage tracking during streaming

### 🔧 Tool & Function Calling
- Define custom tools and functions for AI to use
- Automatic tool execution with result injection
- Type-safe tool definitions with validation
- Support for multiple tool calls in a single response

### 👁️ Multimodal Capabilities
- Text + image input support
- Base64 and URL-based image handling
- Provider-specific vision model routing
- Automatic format conversion and optimization

### 🎯 Smart Routing & Selection
- Decision tree-based provider selection
- Criteria-based routing (cost, speed, capabilities)
- Automatic fallback handling
- Load balancing across multiple providers

### 🔌 Middleware System
- Request/response transformation pipeline
- Custom middleware for logging, caching, validation
- Built-in middleware for common tasks
- Chainable middleware composition

## Quick Start

### Installation
```bash
composer require modelflow-ai/chat modelflow-ai/openai-adapter
```

### Basic Usage
```php
use ModelflowAi\Chat\AIChatRequestHandler;
use ModelflowAi\OpenaiAdapter\Chat\OpenaiChatAdapter;
use ModelflowAi\DecisionTree\DecisionTree;
use ModelflowAi\DecisionTree\DecisionRule;

// Setup
$client = OpenAI::client($_ENV['OPENAI_API_KEY']);
$adapter = new OpenaiChatAdapter($client, 'gpt-4o');
$chatHandler = new AIChatRequestHandler(new DecisionTree([new DecisionRule($adapter)]));

// Simple conversation
$response = $chatHandler->createRequest()
    ->addSystemMessage('You are a helpful assistant.')
    ->addUserMessage('What are the benefits of PHP?')
    ->execute();

echo $response->getMessage()->content;
```

### Streaming Responses
```php
$response = $chatHandler->createStreamedRequest()
    ->addUserMessage('Write a short story about a robot')
    ->execute();

foreach ($response->getMessageStream() as $index => $message) {
    if (0 === $index) {
        echo $message->role->value . ': ';
    }
    echo $message->content;
    flush();
}
```

## Provider Support

### Supported Providers

| Provider          | Models                       | Streaming | Tools | Vision | Local | Notes                |
|-------------------|------------------------------|-----------|-------|--------|-------|----------------------|
| **OpenAI**        | GPT-4, GPT-4 Turbo, GPT-3.5  | ✅         | ✅     | ✅      | ❌     | Full feature support |
| **Anthropic**     | Claude 3 Opus, Sonnet, Haiku | ✅         | ✅     | ✅      | ❌     | Advanced reasoning   |
| **Google Gemini** | Gemini Pro, Gemini Ultra     | ✅         | ✅     | ✅      | ❌     | Multimodal native    |
| **Mistral AI**    | Mistral Large, Medium, Small | ✅         | ✅     | ❌      | ❌     | European provider    |
| **Fireworks.ai**  | 100+ open models             | ✅         | ✅     | ✅      | ❌     | Fast inference       |
| **Ollama**        | Llama, Mistral, Phi, etc.    | ✅         | ⚠️    | ✅      | ✅     | Self-hosted option   |

*⚠️ Limited tool support in Ollama (experimental features)*

### Provider Selection Criteria

The Decision Tree system can automatically select the best provider based on:
- **FeatureCriteria**: Required features (tools, vision, streaming)
- **CapabilityCriteria**: Provider-specific capabilities and performance characteristics
- **Performance**: Response time and throughput requirements
- **Cost**: Token pricing and budget constraints
- **Availability**: Provider status and rate limits
- **Quality**: Model performance for specific tasks

## Best Practices

### Message Management
- Keep conversation context reasonable (< 10 messages for most cases)
- Use system messages for consistent behavior
- Clear message history when changing topics
- Include relevant context in user messages

### Error Handling
- Implement retry logic with exponential backoff
- Handle rate limits gracefully
- Provide fallback responses for failures
- Log errors for monitoring and debugging

### Performance Optimization
- Use streaming for long responses
- Cache common responses when appropriate
- Batch requests when possible
- Monitor token usage and costs

### Security
- Never expose API keys in client-side code
- Validate and sanitize user inputs
- Implement rate limiting for public endpoints
- Use environment variables for configuration

## Documentation Index

### Getting Started
- [Basic Usage](basic-usage.md) - Fundamental chat operations
- [Streaming](streaming.md) - Real-time response streaming
- [Conversations](conversations.md) - Multi-turn conversation management

### Advanced Topics
- [Function Calling](function-calling.md) - Tools and function integration
- [Customization](customization.md) - Custom adapters and middleware

### Examples
- [Simple Chatbot](/examples/chatbot.md) - Basic chatbot implementation
