# Basic Usage

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides a preview of what will be covered in the complete version.

## Simple Chat

### Installation
```bash
composer require modelflow-ai/chat modelflow-ai/openai-adapter
```

### Basic Setup
```php
use ModelflowAi\Chat\AIChatRequestHandler;
use ModelflowAi\OpenaiAdapter\Chat\OpenaiChatAdapter;
use ModelflowAi\DecisionTree\DecisionTree;
use ModelflowAi\DecisionTree\DecisionRule;

$client = OpenAI::client($_ENV['OPENAI_API_KEY']);
$adapter = new OpenaiChatAdapter($client, 'gpt-4o');
$chatHandler = new AIChatRequestHandler(new DecisionTree([new DecisionRule($adapter)]));
```

### Single Message
```php
$response = $chatHandler->createRequest()
    ->addUserMessage('What is PHP?')
    ->execute();

echo $response->getMessage()->content;
```

### System Instructions
```php
$response = $chatHandler->createRequest()
    ->addSystemMessage('You are a helpful programming assistant.')
    ->addUserMessage('Explain object-oriented programming')
    ->execute();

echo $response->getMessage()->content;
```

### Message Types
```php
use ModelflowAi\Chat\Request\Message\AIChatMessage;
use ModelflowAi\Chat\Request\Message\AIChatMessageRoleEnum;

// System message
$systemMessage = AIChatMessage::create(AIChatMessageRoleEnum::SYSTEM, 'You are helpful');

// User message  
$userMessage = AIChatMessage::create(AIChatMessageRoleEnum::USER, 'Hello!');

// Assistant message
$assistantMessage = AIChatMessage::create(AIChatMessageRoleEnum::ASSISTANT, 'Hi there!');
```

## Error Handling

### Basic Error Handling
```php
use ModelflowAi\ApiClient\Transport\TransportException;

try {
    $response = $chatHandler->createRequest()
        ->addUserMessage('Hello!')
        ->execute();
        
    echo $response->getMessage()->content;
} catch (TransportException $e) {
    echo "API Error: " . $e->getMessage();
} catch (\Exception $e) {
    echo "General Error: " . $e->getMessage();
}
```

*Complete documentation coming soon...*