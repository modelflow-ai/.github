# Basic Usage

This guide covers fundamental chat operations in Modelflow AI, from setup to handling responses.

## Installation

```bash
composer require modelflow-ai/chat modelflow-ai/openai-adapter
```

For additional providers:
```bash
composer require modelflow-ai/anthropic-adapter  # Anthropic Claude
composer require modelflow-ai/mistral-adapter    # Mistral AI
composer require modelflow-ai/ollama-adapter     # Local models with Ollama
```

## Basic Setup

### Single Provider Setup

```php
use ModelflowAi\Chat\AIChatRequestHandler;
use ModelflowAi\OpenaiAdapter\Chat\OpenaiChatAdapter;
use ModelflowAi\DecisionTree\DecisionTree;
use ModelflowAi\DecisionTree\DecisionRule;

// Initialize OpenAI client
$client = OpenAI::client($_ENV['OPENAI_API_KEY']);

// Create adapter for specific model
$adapter = new OpenaiChatAdapter($client, 'gpt-4o');

// Create chat handler with decision tree
$decisionTree = new DecisionTree([
    new DecisionRule($adapter)
]);

$chatHandler = new AIChatRequestHandler($decisionTree);
```

### Multiple Provider Setup

```php
use ModelflowAi\DecisionTree\Criteria\FeatureCriteria;
use ModelflowAi\DecisionTree\Criteria\CapabilityCriteria;

// Setup multiple adapters
$openaiAdapter = new OpenaiChatAdapter($openaiClient, 'gpt-4o');
$anthropicAdapter = new AnthropicChatAdapter($anthropicClient, 'claude-3-7-sonnet-latest');
$ollamaAdapter = new OllamaChatAdapter($ollamaClient, 'llama3.2');

// Create decision rules with criteria
$decisionTree = new DecisionTree([
    // Use OpenAI for vision tasks
    new DecisionRule(
        $openaiAdapter,
        [FeatureCriteria::IMAGE_TO_TEXT]
    ),
    // Use Anthropic for advanced reasoning
    new DecisionRule(
        $anthropicAdapter,
        [CapabilityCriteria::ADVANCED]
    ),
    // Use Ollama as fallback for local processing
    new DecisionRule($ollamaAdapter)
]);

$chatHandler = new AIChatRequestHandler($decisionTree);
```

## Creating Chat Requests

### Simple Request

```php
$response = $chatHandler->createRequest()
    ->addUserMessage('What is PHP?')
    ->execute();

echo $response->getMessage()->content;
```

### With System Message

```php
$response = $chatHandler->createRequest()
    ->addSystemMessage('You are a helpful programming assistant specializing in PHP.')
    ->addUserMessage('Explain the difference between abstract classes and interfaces')
    ->execute();

echo $response->getMessage()->content;
```

### With Options

```php
$response = $chatHandler->createRequest()
    ->addSystemMessage('You are a creative writer.')
    ->addUserMessage('Write a haiku about programming')
    ->addOptions([
        'temperature' => 0.9,  // Higher creativity
        'seed' => 42,          // Reproducible output
    ])
    ->execute();
```

### JSON Response Format

Simple JSON format:
```php
$response = $chatHandler->createRequest()
    ->addSystemMessage('You are a helpful assistant that responds in JSON format.')
    ->addUserMessage('Create a user profile for John Doe, age 30, from New York')
    ->asJson()
    ->execute();

echo $response->getMessage()->content;
// Output: {"name": "John Doe", "age": 30, "city": "New York", "id": "user_123"}
```

Structured JSON with schema validation:
```php
$response = $chatHandler->createRequest()
    ->addUserMessage('Analyze the sentiment of: "I love this product!"')
    ->asJson([
        'type' => 'object',
        'properties' => [
            'sentiment' => [
                'type' => 'string',
                'description' => 'The sentiment: positive, negative, or neutral'
            ],
            'confidence' => [
                'type' => 'number',
                'description' => 'Confidence score between 0 and 1'
            ]
        ],
        'required' => ['sentiment', 'confidence']
    ])
    ->execute();

echo $response->getMessage()->content;
// Output: {"sentiment": "positive", "confidence": 0.95}
```

## Message Types

### Creating Messages

```php
use ModelflowAi\Chat\Request\Message\AIChatMessage;
use ModelflowAi\Chat\Request\Message\AIChatMessageRoleEnum;

// System message - Sets behavior and context
$systemMessage = AIChatMessage::create(
    AIChatMessageRoleEnum::SYSTEM, 
    'You are a helpful assistant that speaks like a pirate.'
);

// User message - User input
$userMessage = AIChatMessage::create(
    AIChatMessageRoleEnum::USER, 
    'Tell me about the weather today'
);

// Assistant message - AI response (usually added automatically)
$assistantMessage = AIChatMessage::create(
    AIChatMessageRoleEnum::ASSISTANT, 
    'Ahoy matey! The weather be fine for sailin\' today!'
);

// Tool message - Tool/function results
$toolMessage = AIChatMessage::create(
    AIChatMessageRoleEnum::TOOL,
    'Temperature: 22°C, Sunny',
    ['tool_call_id' => 'call_123']
);
```

### Using Messages in Requests

```php
$response = $chatHandler->createRequest()
    ->addMessage($systemMessage)
    ->addMessage($userMessage)
    ->execute();
```

## Working with Responses

### Response Structure

```php
$response = $chatHandler->createRequest()
    ->addUserMessage('Hello!')
    ->execute();

// Get the message content
$content = $response->getMessage()->content;

// Get the role
$role = $response->getMessage()->role->value; // 'assistant'

// Get usage information
$usage = $response->getUsage();
if ($usage) {
    echo "Prompt tokens: " . $usage->promptTokens . "\n";
    echo "Completion tokens: " . $usage->completionTokens . "\n";
    echo "Total tokens: " . $usage->totalTokens . "\n";
}

// Get metadata (provider-specific data)
$metadata = $response->getMetadata();
```

### Tool Calls

Tool calls are handled automatically by the middleware stack:

```php
use ModelflowAi\Chat\ToolInfo\ToolChoiceEnum;

// Tools are automatically executed when the AI decides to use them
$response = $chatHandler->createRequest()
    ->addUserMessage('What\'s the weather in Paris?')
    ->tool('get_current_weather', new WeatherTool(), 'getCurrentWeather')
    ->toolChoice(ToolChoiceEnum::AUTO)
    ->execute();

// The response already includes the tool execution results
echo $response->getMessage()->content;
// Output: "Based on the current weather data, Paris is sunny with 22°C..."
```

## Next Steps

- Learn about [Streaming](streaming.md) for real-time responses
- Explore [Conversations](conversations.md) for multi-turn chats
- Implement [Function Calling](function-calling.md) for tool integration
- Dive into [Advanced Topics](advanced.md) for customization
