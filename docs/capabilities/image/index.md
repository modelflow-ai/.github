# Image

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides a preview of what will be covered in the complete version.

## Image Overview

Modelflow AI supports both **image generation** (text-to-image) and **image analysis** (vision capabilities) across multiple providers.

**Image Generation:**
- Create images from text descriptions
- Multiple formats and sizes
- High-quality artistic and photorealistic results

**Image Analysis (Vision):**
- Analyze image content using multimodal chat models
- Extract text from images (OCR)
- Understand image context and details

## Core Features

- **🎨 Image Generation** - Create images from text prompts
- **👁️ Vision Analysis** - Understand image content with chat models
- **📐 Multiple Formats** - PNG, JPEG, various sizes
- **🔄 Provider Choice** - OpenAI DALL-E, Fireworks.ai Stable Diffusion
- **💾 Flexible Output** - Stream, Base64, or URL responses

## Quick Start

### Image Generation

#### Installation
```bash
composer require modelflow-ai/image modelflow-ai/openai-adapter
```

#### Basic Usage
```php
use ModelflowAi\Image\AIImageRequestHandler;
use ModelflowAi\OpenaiAdapter\Image\OpenAIImageGenerationAdapter;
use ModelflowAi\Image\Request\Value\ImageFormat;
use ModelflowAi\Image\Middleware\HandleMiddleware;
use ModelflowAi\DecisionTree\DecisionTree;
use ModelflowAi\DecisionTree\DecisionRule;

// Setup
$client = OpenAI::client($_ENV['OPENAI_API_KEY']);
$adapter = new OpenAIImageGenerationAdapter(HttpClient::create(), $client, 'dall-e-3');
$middleware = new HandleMiddleware(new DecisionTree([new DecisionRule($adapter)]));
$imageHandler = new AIImageRequestHandler($middleware);

// Generate an image
$response = $imageHandler->createRequest()
    ->textToImage('A cute robot learning PHP, digital art style')
    ->imageFormat(ImageFormat::PNG)
    ->asStream()
    ->build()
    ->execute();

// Save the image
file_put_contents('robot-learning-php.png', stream_get_contents($response->stream()));
echo "Image saved as robot-learning-php.png";
```

#### Get as Base64
```php
$base64Response = $imageHandler->createRequest()
    ->textToImage('A serene mountain landscape at sunset')
    ->imageFormat(ImageFormat::PNG)
    ->asBase64()
    ->build()
    ->execute();

echo "Base64 image: " . substr($base64Response->base64(), 0, 50) . "...";
```

### Image Analysis (Vision)

Use chat models with vision capabilities:

```php
use ModelflowAi\Chat\Request\Message\AIChatMessage;
use ModelflowAi\Chat\Request\Message\TextPart;
use ModelflowAi\Chat\Request\Message\ImageBase64Part;

// Analyze an image
$response = $chatHandler->createRequest()
    ->addMessage(new AIChatMessage(AIChatMessageRoleEnum::USER, [
        TextPart::create('What do you see in this image?'),
        ImageBase64Part::create($base64ImageData)
    ]))
    ->execute();

echo $response->getMessage()->content;
```

## Supported Providers

### Image Generation
| Provider | Models | Output Formats | Local |
|----------|--------|----------------|-------|
| **OpenAI** | DALL-E 2, DALL-E 3 | PNG, Stream, Base64 | ❌ |
| **Fireworks.ai** | Stable Diffusion XL | PNG, Stream, Base64 | ❌ |

### Image Analysis (Vision)
| Provider | Models | Capabilities | Local |
|----------|--------|--------------|-------|
| **OpenAI** | GPT-4V, GPT-4o | Advanced analysis, OCR | ❌ |
| **Google Gemini** | Gemini Pro Vision | Multimodal understanding | ❌ |
| **Anthropic** | Claude 3 Opus/Sonnet | Image understanding | ❌ |
| **Fireworks.ai** | LLaVA models | Open-source vision | ❌ |
| **Ollama** | LLaVA, Bakllava | Local image analysis | ✅ |

### 5. Use Cases
- **Content Creation** - Blog images, social media
- **Product Catalogs** - Generate product images
- **Document Analysis** - OCR and document understanding
- **Art and Design** - Creative image generation
- **Moderation** - Content safety analysis

## Subpages to Create

- **generation.md** - Image creation from text
- **analysis.md** - Vision and image understanding