# Quick Start

> **Placeholder Content** - This should provide working examples for all four capabilities.

## What Should Be Here

This page should contain working examples for each capability that users can copy-paste and run immediately after installation.

## Structure

### 1. Setup Section
```php
// Common setup code that works for all examples
use ModelflowAi\...

// Basic configuration
$chatHandler = // ... setup code
```

### 2. Chat Example
```php
// Simple chat interaction
$response = $chatHandler->createRequest()
    ->addUserMessage('Hello!')
    ->execute();

echo $response->getMessage()->getContent();
```

### 3. Completion Example  
```php
// Text completion
$response = $completionHandler->createRequest()
    ->setPrompt('Complete this sentence: The future of AI is')
    ->execute();

echo $response->getContent();
```

### 4. Embeddings Example
```php
// Generate embeddings
$response = $embeddingsHandler->createRequest()
    ->addText('Convert this to a vector')
    ->execute();

$vector = $response->getEmbeddings()[0]->getVector();
echo "Generated " . count($vector) . " dimensions";
```

### 5. Image Example
```php
// Generate an image
$response = $imageHandler->createRequest()
    ->setPrompt('A cute robot drawing')
    ->execute();

echo "Image URL: " . $response->getImages()[0]->getUrl();
```

## Content Guidelines

- **All examples must work** with the current API
- Use realistic but simple prompts
- Include output examples
- Keep code minimal but complete
- Add brief explanations of what each example does
- Link to detailed capability documentation