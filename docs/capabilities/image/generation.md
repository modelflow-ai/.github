# Image Generation

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides a preview of what will be covered in the complete version.

## Basic Image Generation

### DALL-E 3 with OpenAI
```php
use ModelflowAi\Image\AIImageRequestHandler;
use ModelflowAi\OpenaiAdapter\Image\OpenAIImageGenerationAdapter;
use ModelflowAi\Image\Request\Value\ImageFormat;

$client = OpenAI::client($_ENV['OPENAI_API_KEY']);
$adapter = new OpenAIImageGenerationAdapter(HttpClient::create(), $client, 'dall-e-3');
$imageHandler = new AIImageRequestHandler($middleware);

$response = $imageHandler->createRequest()
    ->textToImage('A robot learning to code in PHP')
    ->imageFormat(ImageFormat::PNG)
    ->asBase64()
    ->build()
    ->execute();

echo "Generated: " . substr($response->base64(), 0, 50) . "...";
```

### Stable Diffusion with Fireworks.ai
```php
use ModelflowAi\FireworksAiAdapter\Image\FireworksAiImageGenerationAdapter;

$adapter = new FireworksAiImageGenerationAdapter($client, 'stable-diffusion-xl');

$response = $imageHandler->createRequest()
    ->textToImage('Digital art of a futuristic city')
    ->asStream()
    ->build()
    ->execute();

file_put_contents('city.png', stream_get_contents($response->stream()));
```

*Complete documentation coming soon...*