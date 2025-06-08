# Google Gemini Provider

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides a preview of what will be covered in the complete version.

## Overview

Google Gemini provides advanced multimodal AI capabilities with strong vision and reasoning features.

## Supported Capabilities

| Capability | Models | Notes |
|------------|--------|-------|
| **Chat** | gemini-2.0-flash, gemini-1.5-pro | Advanced reasoning |
| **Vision** | gemini-pro-vision | Image understanding via chat |

## Quick Setup

### Installation
```bash
composer require modelflow-ai/google-gemini-adapter
```

### Basic Configuration
```php
use ModelflowAi\GoogleGeminiAdapter\Chat\GoogleGeminiChatAdapter;

$client = GoogleGemini::client($_ENV['GOOGLE_GEMINI_API_KEY']);
$adapter = new GoogleGeminiChatAdapter($client, 'gemini-2.0-flash');
```

### With Symfony Bundle
```yaml
# config/packages/modelflow_ai.yaml
modelflow_ai:
    providers:
        google_gemini:
            enabled: true
            credentials:
                api_key: '%env(GOOGLE_GEMINI_API_KEY)%'
    
    adapters:
        gemini_2_0_flash:
            enabled: true
```

*Complete documentation coming soon...*