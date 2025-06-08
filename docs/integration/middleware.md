# Middleware

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides a preview of what will be covered in the complete version.

## Built-in Middleware

### AdapterDecisionMiddleware
Automatically selects the best adapter based on criteria.

### ToolExecutionMiddleware
Handles automatic function/tool calling.

### ResponseFormatMiddleware
Validates and formats responses according to schemas.

## Custom Middleware

### Logging Middleware
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
        error_log("AI request completed in {$duration}s");
        
        return $response;
    }
}
```

### Rate Limiting Middleware
```php
class RateLimitMiddleware implements AIChatMiddlewareInterface
{
    public function process(
        AIChatRequest $request,
        ?AIChatAdapterInterface $adapter,
        callable $next
    ): AIChatResponseInterface {
        if ($this->isRateLimited()) {
            throw new RateLimitException('Rate limit exceeded');
        }
        
        return $next($request, $adapter);
    }
}
```

*Complete documentation coming soon...*