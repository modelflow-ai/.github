# Fireworks.ai Provider

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides a preview of what will be covered in the complete version.

## Overview

Fireworks.ai provides fast inference with a wide selection of open-source models including Llama, Mistral, and Stable Diffusion.

## Supported Capabilities

| Capability | Models | Notes |
|------------|--------|-------|
| **Chat** | llama-3.1-405b, mixtral-8x7b | Fast inference |
| **Completion** | Various models | Text completion |
| **Embeddings** | nomic-ai/nomic-embed-text-v1.5 | Embeddings |
| **Images** | stable-diffusion-xl-1024-v1-0 | Image generation |

## Quick Setup

### Installation
```bash
composer require modelflow-ai/fireworksai-adapter
```

### Basic Configuration
```php
use ModelflowAi\FireworksAiAdapter\Chat\FireworksAiChatAdapter;

$client = FireworksAi::client($_ENV['FIREWORKSAI_API_KEY']);
$adapter = new FireworksAiChatAdapter($client, 'llama-v3p1-405b-instruct');
```

### With Symfony Bundle
```yaml
# config/packages/modelflow_ai.yaml
modelflow_ai:
    providers:
        fireworksai:
            enabled: true
            credentials:
                api_key: '%env(FIREWORKSAI_API_KEY)%'
    
    adapters:
        llama_405b:
            enabled: true
```

*Complete documentation coming soon...*