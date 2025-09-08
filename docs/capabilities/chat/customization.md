# Custom Adapters & Middleware

Extend Modelflow AI with custom adapters for new providers and middleware for request/response processing.

## Custom Adapters

### Creating an Adapter

```php
use ModelflowAi\Chat\Adapter\AIChatAdapterInterface;
use ModelflowAi\DecisionTree\Criteria\CriteriaCollection;

class MyCustomAdapter implements AIChatAdapterInterface
{
    public function __construct(
        private MyApiClient $client,
        private string $model
    ) {}
    
    public function handleRequest(AIChatRequest $request): AIChatResponseInterface
    {
        if ($request->isStreamed()) {
            return $this->handleStreamedRequest($request);
        }
        
        // Convert request to your API format
        $apiRequest = $this->convertRequest($request);
        
        // Call your API
        $apiResponse = $this->client->chat($apiRequest);
        
        // Convert response back to Modelflow format
        return $this->convertResponse($apiResponse, $request);
    }
    
    public function supports(object $request): bool
    {
        return $request instanceof AIChatRequest;
    }
    
    private function handleStreamedRequest(AIChatRequest $request): AIChatResponseStreamInterface
    {
        $apiRequest = $this->convertRequest($request);
        $stream = $this->client->streamChat($apiRequest);
        
        return new AIChatResponseStream(
            $request,
            $this->createMessageStream($stream)
        );
    }
    
    private function createMessageStream($apiStream): \Generator
    {
        foreach ($apiStream as $chunk) {
            if (isset($chunk['content'])) {
                yield new AIChatResponseMessage(
                    AIChatMessageRoleEnum::ASSISTANT,
                    $chunk['content']
                );
            }
        }
    }
    
    private function convertRequest(AIChatRequest $request): array
    {
        $messages = [];
        
        foreach ($request->getMessages() as $message) {
            $messages[] = [
                'role' => $message->role->value,
                'content' => $message->content,
            ];
        }
        
        return [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => $request->getOption('temperature', 1.0),
        ];
    }
    
    private function convertResponse($apiResponse, AIChatRequest $request): AIChatResponseInterface
    {
        $message = new AIChatResponseMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            $apiResponse['content']
        );
        
        return new AIChatResponse($request, $message, null);
    }
}
```

### Registration

```php
// Register your adapter with the decision tree
$customAdapter = new MyCustomAdapter($client, 'my-model');

$decisionTree = new DecisionTree([
    new DecisionRule($customAdapter),
    // ... other adapters
]);

$chatHandler = new AIChatRequestHandler($decisionTree);
```

## Custom Middleware

### Basic Middleware

```php
use ModelflowAi\Chat\Middleware\AIChatMiddlewareInterface;

class LoggingMiddleware implements AIChatMiddlewareInterface
{
    public function __construct(private LoggerInterface $logger) {}
    
    public function handle(AIChatRequest $request, callable $next): AIChatResponseInterface
    {
        $startTime = microtime(true);
        
        $this->logger->info('Chat request started', [
            'messages' => count($request->getMessages()),
            'has_tools' => $request->hasTools(),
        ]);
        
        // Execute the request
        $response = $next($request);
        
        $duration = microtime(true) - $startTime;
        
        $this->logger->info('Chat request completed', [
            'duration' => $duration,
            'content_length' => strlen($response->getMessage()->content),
        ]);
        
        return $response;
    }
}
```

### Caching Middleware

```php
class CachingMiddleware implements AIChatMiddlewareInterface
{
    public function __construct(
        private CacheInterface $cache,
        private int $ttl = 3600
    ) {}
    
    public function handle(AIChatRequest $request, callable $next): AIChatResponseInterface
    {
        $cacheKey = $this->generateCacheKey($request);
        
        // Check cache
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }
        
        // Execute request
        $response = $next($request);
        
        // Cache response
        $this->cache->set($cacheKey, $response, $this->ttl);
        
        return $response;
    }
    
    private function generateCacheKey(AIChatRequest $request): string
    {
        $messages = [];
        foreach ($request->getMessages() as $message) {
            $messages[] = $message->role->value . ':' . $message->content;
        }
        
        return 'chat:' . md5(json_encode([
            'messages' => $messages,
            'options' => $request->getOptions(),
        ]));
    }
}
```

### Security Middleware

```php
class SecurityMiddleware implements AIChatMiddlewareInterface
{
    public function handle(AIChatRequest $request, callable $next): AIChatResponseInterface
    {
        // Validate input
        foreach ($request->getMessages() as $message) {
            if ($message->role === AIChatMessageRoleEnum::USER) {
                $this->validateContent($message->content);
            }
        }
        
        // Execute request
        $response = $next($request);
        
        // Sanitize output
        $sanitizedContent = $this->sanitizeOutput($response->getMessage()->content);
        
        if ($sanitizedContent !== $response->getMessage()->content) {
            // Return modified response
            $newMessage = new AIChatResponseMessage(
                $response->getMessage()->role,
                $sanitizedContent
            );
            
            return new AIChatResponse(
                $response->getRequest(),
                $newMessage,
                $response->getUsage(),
                $response->getMetadata()
            );
        }
        
        return $response;
    }
    
    private function validateContent(string $content): void
    {
        if (strlen($content) > 10000) {
            throw new InvalidArgumentException('Message too long');
        }
        
        // Check for malicious patterns
        if (preg_match('/\b(script|eval|exec)\b/i', $content)) {
            throw new SecurityException('Potentially malicious content detected');
        }
    }
    
    private function sanitizeOutput(string $content): string
    {
        // Remove sensitive patterns from AI response
        $content = preg_replace('/\b\d{3}-\d{2}-\d{4}\b/', '[REDACTED]', $content); // SSN
        $content = preg_replace('/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/', '[EMAIL]', $content); // Email
        
        return $content;
    }
}
```

### Middleware Registration

```php
$chatHandler = new AIChatRequestHandler(
    $decisionTree,
    [
        new SecurityMiddleware(),      // First - validate input
        new LoggingMiddleware($logger), // Second - log activity
        new CachingMiddleware($cache),  // Third - check cache
        // AdapterExecutionMiddleware is added automatically last
    ]
);
```

## Best Practices

### Adapter Development
- **Error Handling**: Handle API errors gracefully and return meaningful error responses
- **Feature Support**: Implement proper `supports()` logic based on your API capabilities  
- **Rate Limiting**: Respect API rate limits and implement backoff strategies
- **Testing**: Write comprehensive tests for all adapter functionality

### Middleware Development
- **Single Responsibility**: Each middleware should have one clear purpose
- **Order Matters**: Consider middleware execution order carefully
- **Performance**: Keep middleware lightweight to avoid impacting response times
- **Error Handling**: Always call `$next($request)` unless explicitly blocking the request

### Security Considerations
- **Input Validation**: Always validate and sanitize user inputs
- **Output Filtering**: Filter sensitive information from AI responses
- **Authentication**: Implement proper authentication for custom adapters
- **Logging**: Log security events for monitoring and auditing

## Next Steps

- Review [Basic Usage](basic-usage.md) for core concepts
- Explore [Function Calling](function-calling.md) for tool integration
- Learn [Streaming](streaming.md) for real-time responses
