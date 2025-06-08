# Basic Usage

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides a preview of what will be covered in the complete version.

## Simple Completion

### Installation
```bash
composer require modelflow-ai/completion modelflow-ai/ollama-adapter
```

### Basic Setup
```php
use ModelflowAi\Completion\AICompletionRequestHandler;
use ModelflowAi\DecisionTree\DecisionRule;
use ModelflowAi\DecisionTree\DecisionTree;
use ModelflowAi\Ollama\Ollama;
use ModelflowAi\OllamaAdapter\Completion\OllamaCompletionAdapter;

$client = Ollama::client();
$adapter = new OllamaCompletionAdapter($client, 'llama3.2');
$completionHandler = new AICompletionRequestHandler(new DecisionTree([new DecisionRule($adapter)]));
```

### Simple Completion
```php
$response = $completionHandler->createRequest('Complete this sentence: PHP is')
    ->build()
    ->execute();

echo $response->getContent();
// Output: a popular server-side scripting language used for web development...
```

### With Parameters
```php
$response = $completionHandler->createRequest('Write a function to calculate fibonacci:')
    ->withMaxTokens(200)
    ->withTemperature(0.7)
    ->build()
    ->execute();

echo $response->getContent();
```

## Request Building

### Basic Request
```php
$request = $completionHandler->createRequest('The future of AI is')
    ->build();
    
$response = $request->execute();
```

### With Stop Sequences
```php
$response = $completionHandler->createRequest('List programming languages:')
    ->withMaxTokens(100)
    ->withStopSequences(['\n\n', 'Note:'])
    ->build()
    ->execute();

echo $response->getContent();
```

### Multiple Completions
```php
$response = $completionHandler->createRequest('Creative story idea:')
    ->withMaxTokens(50)
    ->withTemperature(0.9)
    ->withN(3) // Generate 3 different completions
    ->build()
    ->execute();

foreach ($response->getCompletions() as $completion) {
    echo "Option: " . $completion->getContent() . "\n";
}
```

## Common Use Cases

### Code Completion
```php
$codePrompt = <<<PHP
function calculateTax(\$price, \$rate) {
    // Complete this function
PHP;

$response = $completionHandler->createRequest($codePrompt)
    ->withMaxTokens(100)
    ->withTemperature(0.1) // Low temperature for code
    ->build()
    ->execute();

echo $response->getContent();
```

### Text Generation
```php
$response = $completionHandler->createRequest('Write a professional email to apologize for a delay:')
    ->withMaxTokens(150)
    ->withTemperature(0.7)
    ->build()
    ->execute();

echo $response->getContent();
```

### Template Filling
```php
$template = <<<TEXT
Product Review Template:

Product: [PRODUCT_NAME]
Rating: [RATING]/5
Pros:
- 
TEXT;

$response = $completionHandler->createRequest($template)
    ->withMaxTokens(100)
    ->build()
    ->execute();

echo $response->getContent();
```

## Error Handling

### Basic Error Handling
```php
use ModelflowAi\ApiClient\Transport\TransportException;

try {
    $response = $completionHandler->createRequest('Complete this: AI will')
        ->build()
        ->execute();
        
    echo $response->getContent();
} catch (TransportException $e) {
    echo "API Error: " . $e->getMessage();
} catch (\Exception $e) {
    echo "General Error: " . $e->getMessage();
}
```

*Complete documentation coming soon...*