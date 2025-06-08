# Ollama Provider

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides a preview of what will be covered in the complete version.

## Overview

Ollama enables running AI models locally on your machine. Perfect for privacy-sensitive applications, development, and avoiding API costs.

## Supported Capabilities

| Capability | Models | Notes |
|------------|--------|-------|
| **Chat** | llama3.2, llama3, llama2, codellama | Local conversation models |
| **Completion** | All chat models | Text completion support |
| **Embeddings** | mxbai-embed-large, nomic-embed-text | Local embeddings |
| **Vision** | llava, bakllava | Local image analysis |

## Quick Setup

### Installation
```bash
# Install Ollama first
curl -fsSL https://ollama.ai/install.sh | sh

# Pull a model
ollama pull llama3.2

# Install adapter
composer require modelflow-ai/ollama-adapter
```

### Basic Configuration
```php
use ModelflowAi\OllamaAdapter\Chat\OllamaChatAdapter;
use ModelflowAi\Ollama\Ollama;

$client = Ollama::client('http://localhost:11434');
$adapter = new OllamaChatAdapter($client, 'llama3.2');
```

### With Symfony Bundle
```yaml
# config/packages/modelflow_ai.yaml
modelflow_ai:
    providers:
        ollama:
            enabled: true
            url: '%env(OLLAMA_URL)%/'
    
    adapters:
        llama3_2:
            enabled: true
```

## Features

- **Complete privacy** - runs locally
- **No API costs** - free to use
- **Multiple model support**
- **Vision capabilities** with LLaVA
- **Embeddings support**
- **Custom model support**

*Complete documentation coming soon...*