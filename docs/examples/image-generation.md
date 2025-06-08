# Image Generation App

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides a preview of what will be covered in the complete version.

## Image Generation Service

### Basic Image Generator
```php
class ImageGenerationService
{
    public function __construct(
        private AIImageRequestHandlerInterface $imageHandler
    ) {}
    
    public function generateImage(string $prompt, array $options = []): string
    {
        $response = $this->imageHandler->createRequest()
            ->textToImage($prompt)
            ->imageFormat($options['format'] ?? ImageFormat::PNG)
            ->asBase64()
            ->build()
            ->execute();
            
        return $response->base64();
    }
    
    public function generateAndSave(string $prompt, string $filename): void
    {
        $response = $this->imageHandler->createRequest()
            ->textToImage($prompt)
            ->asStream()
            ->build()
            ->execute();
            
        file_put_contents($filename, stream_get_contents($response->stream()));
    }
}
```

### Web Interface
```php
class ImageController
{
    #[Route('/generate-image', methods: ['POST'])]
    public function generate(Request $request): JsonResponse
    {
        $prompt = $request->get('prompt');
        
        try {
            $base64Image = $this->imageService->generateImage($prompt);
            
            return $this->json([
                'success' => true,
                'image' => 'data:image/png;base64,' . $base64Image
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
```

*Complete documentation coming soon...*