# Chat

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides a preview of what will be covered in the complete version.

## Chat Overview

Chat is Modelflow AI's conversational capability, designed for multi-turn conversations with context awareness. Use Chat for complex interactions where the AI needs to remember previous messages and maintain conversation state.

**When to use Chat:**
- Building chatbots and virtual assistants
- Multi-turn conversations requiring context
- Complex reasoning tasks
- Tool/function calling scenarios
- Vision tasks (image analysis)

**When to use Completion instead:**
- Single-shot text generation
- Simple text completion tasks
- Template filling scenarios

## Core Features

- **🗨️ Conversational AI** - Multi-turn conversations with context
- **⚡ Streaming** - Real-time response streaming  
- **🔧 Tool Calling** - Function execution and tool integration
- **👁️ Vision** - Text + images in conversations (multimodal)
- **🔄 Provider Agnostic** - Works with any AI provider
- **🎯 Smart Routing** - Automatic provider selection based on criteria

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

## Supported Providers

| Provider | Chat | Streaming | Tools | Vision | Local |
|----------|------|-----------|-------|--------|-------|
| **OpenAI** | ✅ | ✅ | ✅ | ✅ | ❌ |
| **Anthropic** | ✅ | ✅ | ✅ | ✅ | ❌ |
| **Google Gemini** | ✅ | ✅ | ✅ | ✅ | ❌ |
| **Mistral AI** | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Fireworks.ai** | ✅ | ✅ | ✅ | ✅ | ❌ |
| **Ollama** | ✅ | ✅ | ⚠️ | ✅ | ✅ |

*⚠️ Limited tool support*

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