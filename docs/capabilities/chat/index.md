# Chat

> **Placeholder Content** - This should be the comprehensive Chat capability documentation.

## What Should Be Here

### 1. Chat Overview
- What is the Chat capability
- When to use Chat vs Completion
- Key features and benefits

### 2. Core Features
- **Conversational AI** - Multi-turn conversations
- **Streaming** - Real-time response streaming  
- **Function Calling** - Tool integration and execution
- **Multi-modal** - Text + images in conversations
- **Provider Agnostic** - Works with any AI provider

### 3. Quick Start
```php
// Simple chat example with correct API
use ModelflowAi\Chat\AIChatRequestHandlerInterface;

$response = $chatHandler->createRequest()
    ->addUserMessage('Hello!')
    ->execute();

echo $response->getMessage()->getContent();
```

### 4. Supported Providers
Table showing which providers support which chat features:
- OpenAI GPT (streaming, functions, vision)
- Anthropic Claude (streaming, functions, vision)
- Google Gemini (streaming, functions, vision)
- Mistral AI (streaming, functions)
- Fireworks.ai (streaming, functions, vision)
- Ollama (streaming, limited functions, vision)

### 5. Installation & Configuration
- Package installation
- Provider setup
- Symfony configuration
- Standalone usage

### 6. Common Use Cases
- Customer support bots
- Virtual assistants
- Code generation
- Document analysis
- Creative writing

## Subpages to Create

- **basic-usage.md** - Fundamental chat operations
- **streaming.md** - Real-time response streaming
- **function-calling.md** - Tools and function integration
- **conversations.md** - Multi-turn conversation management