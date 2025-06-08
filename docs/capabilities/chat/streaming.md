# Streaming

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides a preview of what will be covered in the complete version.

## Streaming Responses

Get responses as they're generated for better user experience.

### Basic Streaming
```php
$response = $chatHandler->createStreamedRequest()
    ->addUserMessage('Write a short story about AI')
    ->execute();

foreach ($response->getMessageStream() as $index => $message) {
    if (0 === $index) {
        echo $message->role->value . ': ';
    }
    echo $message->content;
    flush(); // Send to browser immediately
}
```

### Streaming with System Message
```php
$response = $chatHandler->createStreamedRequest()
    ->addSystemMessage('You are a creative writer')
    ->addUserMessage('Write a haiku about programming')
    ->execute();

foreach ($response->getMessageStream() as $index => $message) {
    echo $message->content;
    flush();
}
```

## Web Browser Streaming

### Server-Sent Events (SSE)
```php
// In your controller
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

$response = $chatHandler->createStreamedRequest()
    ->addUserMessage($_POST['message'])
    ->execute();

foreach ($response->getMessageStream() as $index => $message) {
    echo "data: " . json_encode([
        'content' => $message->content,
        'done' => false
    ]) . "\n\n";
    flush();
}

echo "data: " . json_encode(['done' => true]) . "\n\n";
flush();
```

### JavaScript Client
```javascript
const eventSource = new EventSource('/chat/stream');

eventSource.onmessage = function(event) {
    const data = JSON.parse(event.data);
    
    if (data.done) {
        eventSource.close();
        return;
    }
    
    document.getElementById('response').innerHTML += data.content;
};
```

## Streaming with Tool Calls

### Function Calling in Streams
```php
$response = $chatHandler->createStreamedRequest()
    ->addUserMessage('What is the weather in Paris?')
    ->addTool(new WeatherTool())
    ->execute();

foreach ($response->getMessageStream() as $index => $message) {
    if ($message->role === AIChatMessageRoleEnum::ASSISTANT) {
        echo $message->content;
    } elseif ($message->role === AIChatMessageRoleEnum::TOOL) {
        echo "[Tool executed: " . $message->toolName . "]";
    }
    flush();
}
```

## Performance Considerations

- **Buffer management** - Use `flush()` to send data immediately
- **Connection handling** - Properly close SSE connections
- **Error handling** - Handle network interruptions gracefully
- **Rate limiting** - Consider API rate limits for streaming

*Complete documentation coming soon...*