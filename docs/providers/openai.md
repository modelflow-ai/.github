# OpenAI Provider

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides a preview of what will be covered in the complete version.

## Overview

OpenAI provides some of the most advanced AI models including GPT-4o, DALL-E 3, and text-embedding models. This provider offers excellent performance for chat, embeddings, and image generation.

## Supported Capabilities

| Capability | Models | Notes |
|------------|--------|-------|
| **Chat** | gpt-4o, gpt-4, gpt-3.5-turbo | Advanced reasoning, tool calling, vision |
| **Embeddings** | text-embedding-3-small/large, ada-002 | High-quality semantic embeddings |
| **Images** | dall-e-3, dall-e-2 | High-quality image generation |

## Quick Setup

### Installation
```bash
composer require modelflow-ai/openai-adapter
```

### Basic Configuration
```php
use ModelflowAi\OpenaiAdapter\Chat\OpenaiChatAdapter;

$client = OpenAI::client($_ENV['OPENAI_API_KEY']);
$adapter = new OpenaiChatAdapter($client, 'gpt-4o');
```

### With Symfony Bundle
```yaml
# config/packages/modelflow_ai.yaml
modelflow_ai:
    providers:
        openai:
            enabled: true
            credentials:
                api_key: '%env(OPENAI_API_KEY)%'
    
    adapters:
        gpt4o:
            enabled: true
```

## Features

- **Advanced reasoning** with GPT-4o
- **Function/tool calling** support
- **Vision capabilities** (image analysis)
- **Streaming responses**
- **High-quality embeddings**
- **DALL-E image generation**

*Complete documentation coming soon...*