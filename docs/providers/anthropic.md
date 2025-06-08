# Anthropic Provider

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides a preview of what will be covered in the complete version.

## Overview

Anthropic's Claude models are known for safety, long context windows, and detailed analysis. Perfect for complex reasoning tasks and content analysis.

## Supported Capabilities

| Capability | Models | Notes |
|------------|--------|-------|
| **Chat** | claude-3-5-sonnet, claude-3-opus, claude-3-haiku | Long context, safety-focused |
| **Vision** | claude-3-opus, claude-3-sonnet | Image understanding via chat |

## Quick Setup

### Installation
```bash
composer require modelflow-ai/anthropic-adapter
```

### Basic Configuration
```php
use ModelflowAi\AnthropicAdapter\Chat\AnthropicChatAdapter;

$client = Anthropic::client($_ENV['ANTHROPIC_API_KEY']);
$adapter = new AnthropicChatAdapter($client, 'claude-3-5-sonnet');
```

### With Symfony Bundle
```yaml
# config/packages/modelflow_ai.yaml
modelflow_ai:
    providers:
        anthropic:
            enabled: true
            credentials:
                api_key: '%env(ANTHROPIC_API_KEY)%'
    
    adapters:
        claude_3_5_sonnet:
            enabled: true
```

## Features

- **Long context windows** (up to 200K tokens)
- **Safety-focused** responses
- **Vision capabilities** (image analysis)
- **Streaming support**
- **Advanced reasoning**

*Complete documentation coming soon...*