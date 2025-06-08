# Core Concepts

Understanding these fundamental concepts will help you make the most of Modelflow AI. Each concept builds on the others to create a flexible, powerful system for AI integration.

## Providers

**Providers** are the AI service vendors that power your applications - OpenAI, Anthropic, Ollama, Mistral, and others.

### What Are Providers?
Think of providers as different AI "engines" with unique strengths:
- **OpenAI** - Advanced reasoning, tool calling, multimodal
- **Anthropic** - Long context, safety-focused, detailed analysis  
- **Ollama** - Local models, privacy, no API costs
- **Mistral** - European option, efficient models
- **Google Gemini** - Multimodal capabilities, large context
- **Fireworks.ai** - Fast inference, wide model selection, all capabilities

### Provider Abstraction
Modelflow AI abstracts away provider differences through common interfaces:

```php
// Same code works with any provider
$response = $chatHandler->createRequest()
    ->addUserMessage('Explain quantum computing')
    ->execute();

// Whether you're using OpenAI, Claude, or Llama
```

### Provider Capabilities
Different providers support different features:

| Provider | Chat | Completion | Embeddings | Images | Local |
|----------|------|------------|------------|--------|-------|
| OpenAI | ✅ | ✅ | ✅ | ✅ | ❌ |
| Anthropic | ✅ | ❌ | ❌ | ❌ | ❌ |
| Ollama | ✅ | ✅ | ✅ | ❌ | ✅ |
| Mistral | ✅ | ❌ | ✅ | ❌ | ❌ |
| Fireworks.ai | ✅ | ✅ | ✅ | ✅ | ❌ |
| Google Gemini | ✅ | ❌ | ❌ | ❌ | ❌ |

## Adapters

**Adapters** are the bridge between Modelflow AI's unified interface and each provider's unique API.

### How Adapters Work
Each adapter translates between:
1. **Modelflow AI's common format** → **Provider's specific format**
2. **Provider's response** → **Modelflow AI's common format**

```php
// This request works the same for all providers...
$request = $chatHandler->createRequest()
    ->addUserMessage('Hello');

// But each adapter handles it differently:
// - OpenaiChatAdapter → OpenAI API format
// - AnthropicChatAdapter → Anthropic API format  
// - OllamaChatAdapter → Ollama API format
```

### Adapter Types
Each AI capability has its own adapter interface:

- **`AIChatAdapterInterface`** - Conversational AI with context
- **`AICompletionAdapterInterface`** - Single-shot text completion
- **`EmbeddingAdapterInterface`** - Text-to-vector conversion
- **`AIImageAdapterInterface`** - Text-to-image generation

### Adapter Configuration
Adapters are created through factories with provider-specific options:

```php
// OpenAI adapter with specific model
$adapter = new OpenaiChatAdapter($client, 'gpt-4o');

// Ollama adapter with custom URL
$adapter = new OllamaChatAdapter(
    Ollama::client('http://localhost:11434'), 
    'llama3.2'
);
```

## Request Handlers

**Request Handlers** are your main entry points - the objects you interact with to make AI requests.

### Handler Types
Each AI capability has a dedicated handler:

```php
// Chat - conversational AI with context
$chatHandler->createRequest()
    ->addSystemMessage('You are a helpful assistant')
    ->addUserMessage('What is PHP?')
    ->execute();

// Completion - single text completion
$completionHandler->createRequest('Complete this sentence: PHP is')
    ->build()
    ->execute();

// Embeddings - convert text to vectors
$embeddingsHandler->createSimilarityRequest('search query', 'store')
    ->withLimit(5)
    ->execute();

// Images - generate images from text
$imageHandler->createRequest()
    ->textToImage('A cute robot learning PHP')
    ->imageFormat(ImageFormat::PNG)
    ->execute();
```

### Request/Response Pattern
All handlers follow a consistent builder pattern:

1. **Create** - Start building a request
2. **Configure** - Add messages, criteria, options
3. **Execute** - Send to AI provider and get response

```php
$response = $handler
    ->createRequest()           // Create
    ->addUserMessage('Hello')   // Configure  
    ->withCriteria($criteria)   // Configure
    ->execute();                // Execute
```

### Error Handling
Handlers use specific exceptions for different error types:
- **`TransportException`** - Network/API errors
- **`InvalidDsnException`** - Configuration errors
- **`\Exception`** - General errors (no matching adapter)

## Decision Tree

The **Decision Tree** automatically selects the best adapter for each request based on your criteria.

### How It Works
Instead of manually choosing adapters, you define rules:

```php
$decisionTree = new DecisionTree([
    // Rule 1: Use OpenAI for advanced tasks
    new DecisionRule($openaiAdapter, [
        CapabilityCriteria::ADVANCED,
        FeatureCriteria::TOOLS
    ]),
    
    // Rule 2: Use Ollama for privacy-sensitive tasks
    new DecisionRule($ollamaAdapter, [
        PrivacyCriteria::HIGH
    ]),
    
    // Rule 3: Use Anthropic as fallback
    new DecisionRule($anthropicAdapter, [
        CapabilityCriteria::BASIC
    ])
]);
```

### Criteria Types
Control adapter selection with built-in criteria:

- **`CapabilityCriteria`** - `BASIC`, `INTERMEDIATE`, `ADVANCED`, `SMART`
- **`PrivacyCriteria`** - `LOW`, `MEDIUM`, `HIGH`
- **`FeatureCriteria`** - `IMAGE_TO_TEXT`, `TOOLS`, `STREAM`
- **`ProviderCriteria`** - Force specific providers
- **`ModelCriteria`** - Force specific models

### Request-Level Selection
Add criteria to your request to influence adapter selection:

```php
// The decision tree will match these criteria against configured rules
$response = $chatHandler->createRequest()
    ->addUserMessage('Complex reasoning task')
    ->withCriteria(CapabilityCriteria::ADVANCED, FeatureCriteria::TOOLS)
    ->execute();

// The decision tree will:
// 1. Look for adapters matching BOTH criteria
// 2. Select the first matching adapter based on rule order
// 3. If no adapter matches all criteria, throw an exception
```

### Fallback Strategy
Rules are evaluated in order:
1. First matching rule wins
2. If no rules match, an exception is thrown
3. Place broader criteria (fallbacks) at the end

## Middleware

**Middleware** intercepts requests and responses, allowing you to add cross-cutting functionality.

### How Middleware Works
Middleware forms a pipeline around your AI requests:

```mermaid
flowchart LR
    A[Request] --> B[Middleware 1] --> C[Middleware 2] --> D[Adapter] --> E[Provider API]
    E --> F[Response] --> G[Middleware 2] --> H[Middleware 1] --> I[Final Response]
    
    classDef request fill:#4CAF50,color:#fff
    classDef middleware fill:#2196F3,color:#fff
    classDef provider fill:#FF9800,color:#fff
    
    class A,I request
    class B,C,G,H middleware
    class D,E provider
```

### Built-in Middleware
Modelflow AI includes essential middleware:

- **`AdapterDecisionMiddleware`** - Selects adapter using Decision Tree
- **`AdapterExecutionMiddleware`** - Executes the actual adapter
- **`ToolExecutionMiddleware`** - Handles automatic function calling
- **`ResponseFormatMiddleware`** - Enforces JSON schemas

### Custom Middleware
Add your own logic to the pipeline:

```php
class LoggingMiddleware implements AIChatMiddlewareInterface
{
    public function process(
        AIChatRequest $request,
        ?AIChatAdapterInterface $adapter,
        callable $next
    ): AIChatResponseInterface {
        $start = microtime(true);
        
        $response = $next($request, $adapter);
        
        $duration = microtime(true) - $start;
        $this->logger->info("AI request completed in {$duration}s");
        
        return $response;
    }
}
```

## Message Types

For chat interactions, Modelflow AI uses structured **Messages** to represent different participants and content types.

### Message Roles
Each message has a specific role in the conversation:

```php
// System message - sets AI behavior
AIChatMessage::create(AIChatMessageRoleEnum::SYSTEM, 'You are a helpful assistant');

// User message - human input
AIChatMessage::create(AIChatMessageRoleEnum::USER, 'What is PHP?');

// Assistant message - AI response
AIChatMessage::create(AIChatMessageRoleEnum::ASSISTANT, 'PHP is a programming language...');

// Tool message - function call results
AIChatMessage::create(AIChatMessageRoleEnum::TOOL, $toolResult);
```

### Content Types
Messages can contain different types of content:

**Text Content:**
```php
AIChatMessage::createUserMessage('Hello world!');
```

**Multimodal Content:**
```php
new AIChatMessage(AIChatMessageRoleEnum::USER, [
    TextPart::create('What do you see in this image?'),
    ImageBase64Part::create($base64ImageData)
]);
```

**Tool Calls:**
```php
// AI can call functions
new AIChatMessage(AIChatMessageRoleEnum::ASSISTANT, [
    TextPart::create('Let me check the weather for you'),
    ToolCallPart::create('get_weather', ['city' => 'Paris'])
]);
```

### Message History
Chat handlers maintain conversation context:

```php
$response = $chatHandler->createRequest()
    ->addSystemMessage('You are a helpful assistant')
    ->addUserMessage('What is 2+2?')
    ->addAssistantMessage('2+2 equals 4.')
    ->addUserMessage('What about 3+3?')  // Continues conversation
    ->execute();
```

## Configuration Patterns

Modelflow AI supports multiple configuration approaches depending on your needs.

### Symfony Bundle Configuration
For Symfony applications, use YAML configuration:

```yaml
# config/packages/modelflow_ai.yaml
modelflow_ai:
    providers:
        openai:
            enabled: true
            credentials:
                api_key: '%env(OPENAI_API_KEY)%'
        ollama:
            enabled: true
            url: '%env(OLLAMA_URL)%'
    
    adapters:
        gpt4o:
            enabled: true
            criteria:
                - !php/const ModelflowAi\DecisionTree\Criteria\CapabilityCriteria::ADVANCED
                - !php/const ModelflowAi\DecisionTree\Criteria\FeatureCriteria::TOOLS
        llama32:
            enabled: true
            criteria:
                - !php/const ModelflowAi\DecisionTree\Criteria\PrivacyCriteria::HIGH
```

### Standalone Configuration
For non-Symfony applications, configure programmatically:

```php
// Create provider clients
$openai = OpenAI::client($_ENV['OPENAI_API_KEY']);
$ollama = Ollama::client('http://localhost:11434');

// Create adapters
$gptAdapter = new OpenaiChatAdapter($openai, 'gpt-4o');
$llamaAdapter = new OllamaChatAdapter($ollama, 'llama3.2');

// Set up decision tree
$decisionTree = new DecisionTree([
    new DecisionRule($gptAdapter, [CapabilityCriteria::ADVANCED]),
    new DecisionRule($llamaAdapter, [PrivacyCriteria::HIGH])
]);

// Create handler
$chatHandler = new AIChatRequestHandler(/* middleware with decision tree */);
```

### Environment Variables
Keep sensitive data in environment variables:

```bash
# .env
OPENAI_API_KEY=sk-...
ANTHROPIC_API_KEY=sk-ant-...
OLLAMA_URL=http://localhost:11434
MISTRAL_API_KEY=...
```

## Common Patterns

### Streaming Responses
Get responses as they're generated for better user experience:

```php
$response = $chatHandler->createStreamedRequest()
    ->addUserMessage('Write a long story')
    ->execute();

foreach ($response->getMessageStream() as $index => $message) {
    if (0 === $index) {
        echo $message->role->value . ': ';
    }
    echo $message->content;
    flush(); // Send to browser immediately
}
```

### Error Handling with Retries
Handle transient errors gracefully:

```php
$maxRetries = 3;
$attempt = 0;

while ($attempt < $maxRetries) {
    try {
        $response = $chatHandler->createRequest()
            ->addUserMessage('Hello')
            ->execute();
        break; // Success
    } catch (TransportException $e) {
        $attempt++;
        if ($attempt >= $maxRetries) {
            throw $e; // Give up
        }
        sleep(pow(2, $attempt)); // Exponential backoff
    }
}
```

### Response Format Enforcement
Ensure AI responses match your expected structure:

```php
$response = $chatHandler->createRequest()
    ->addUserMessage('Extract info about this person: John Doe, age 30')
    ->addResponseFormat(new JsonSchemaResponseFormat([
        'type' => 'object',
        'properties' => [
            'name' => ['type' => 'string'],
            'age' => ['type' => 'integer']
        ],
        'required' => ['name', 'age']
    ]))
    ->execute();

$data = json_decode($response->getMessage()->content, true);
// Guaranteed to have 'name' and 'age' fields
```

### Caching for Performance
Cache expensive operations like embeddings:

```php
use ModelflowAi\Embeddings\Adapter\Cache\CacheEmbeddingAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

$cache = new FilesystemAdapter('embeddings', 3600); // 1 hour TTL
$cachedAdapter = new CacheEmbeddingAdapter($originalAdapter, $cache);
```

## Putting It All Together

These concepts work together to create a flexible AI integration:

1. **Providers** give you access to different AI capabilities
2. **Adapters** normalize provider differences  
3. **Request Handlers** provide consistent interfaces
4. **Decision Tree** automatically selects the best provider
5. **Middleware** adds cross-cutting functionality
6. **Messages** structure conversations
7. **Configuration** sets up your environment
8. **Common Patterns** solve real-world challenges

This layered approach means you can start simple and add complexity only when needed, while maintaining the flexibility to switch providers, add new capabilities, and customize behavior at any level.

## Next Steps

Now that you understand the core concepts:

- **[Architecture](./architecture)** - See how these concepts fit together
- **[Chat Documentation](/capabilities/chat/)** - Dive deep into conversations
- **[Provider Comparison](/providers/)** - Choose the right AI provider
- **[Integration Guide](/integration/)** - Set up your framework