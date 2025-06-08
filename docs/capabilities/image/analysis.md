# Image Analysis

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides a preview of what will be covered in the complete version.

## Vision Models

### Analyzing Images with GPT-4V
```php
use ModelflowAi\Chat\Request\Message\AIChatMessage;
use ModelflowAi\Chat\Request\Message\TextPart;
use ModelflowAi\Chat\Request\Message\ImageBase64Part;

$imageData = base64_encode(file_get_contents('image.jpg'));

$response = $chatHandler->createRequest()
    ->addMessage(new AIChatMessage(AIChatMessageRoleEnum::USER, [
        TextPart::create('What objects do you see in this image?'),
        ImageBase64Part::create($imageData)
    ]))
    ->execute();

echo $response->getMessage()->content;
```

### OCR with Vision Models
```php
$response = $chatHandler->createRequest()
    ->addMessage(new AIChatMessage(AIChatMessageRoleEnum::USER, [
        TextPart::create('Extract all text from this image'),
        ImageBase64Part::create($imageData)
    ]))
    ->execute();

echo "Extracted text: " . $response->getMessage()->content;
```

*Complete documentation coming soon...*