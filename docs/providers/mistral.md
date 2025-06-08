# Mistral AI Provider

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides a preview of what will be covered in the complete version.

## Overview

Mistral AI provides efficient European AI models with strong performance and competitive pricing.

## Supported Capabilities

| Capability | Models | Notes |
|------------|--------|-------|
| **Chat** | mistral-large, mistral-medium, mistral-small | Efficient reasoning |
| **Embeddings** | mistral-embed | High-quality embeddings |

## Quick Setup

### Installation
```bash
composer require modelflow-ai/mistral-adapter
```

### Basic Configuration
```php
use ModelflowAi\MistralAdapter\Chat\MistralChatAdapter;

$client = Mistral::client($_ENV['MISTRAL_API_KEY']);
$adapter = new MistralChatAdapter($client, 'mistral-large');
```

### With Symfony Bundle
```yaml
# config/packages/modelflow_ai.yaml
modelflow_ai:
    providers:
        mistral:
            enabled: true
            credentials:
                api_key: '%env(MISTRAL_API_KEY)%'
    
    adapters:
        mistral_large:
            enabled: true
```

*Complete documentation coming soon...*