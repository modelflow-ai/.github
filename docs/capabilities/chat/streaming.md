# Streaming

Streaming provides real-time AI responses as they're generated, improving user experience and handling long outputs efficiently.

## Basic Usage

```php
$response = $chatHandler->createStreamedRequest()
    ->addUserMessage('Write a short story about AI')
    ->execute();

foreach ($response->getMessageStream() as $chunk) {
    echo $chunk->content;
    flush();
}
```

## Web Integration

### Server-Sent Events (SSE)

```php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

$response = $chatHandler->createStreamedRequest()
    ->addUserMessage($_POST['message'])
    ->execute();

foreach ($response->getMessageStream() as $chunk) {
    echo "data: " . json_encode(['content' => $chunk->content]) . "\n\n";
    flush();
}

echo "data: [DONE]\n\n";
```

### JavaScript Client

```javascript
const eventSource = new EventSource('/chat/stream');

eventSource.onmessage = function(event) {
    if (event.data === '[DONE]') {
        eventSource.close();
        return;
    }
    
    const data = JSON.parse(event.data);
    document.getElementById('response').innerHTML += data.content;
};
```

## Streaming with Tools

```php
$response = $chatHandler->createStreamedRequest()
    ->addUserMessage('What is the weather in Paris?')
    ->tool('get_weather', new WeatherTool(), 'getCurrentWeather')
    ->execute();

foreach ($response->getMessageStream() as $chunk) {
    echo $chunk->content;
    flush();
}
```

## Error Handling

```php
try {
    $response = $chatHandler->createStreamedRequest()
        ->addUserMessage('Generate a long story')
        ->execute();
    
    foreach ($response->getMessageStream() as $chunk) {
        echo $chunk->content;
        flush();
    }
} catch (Exception $e) {
    echo "Stream error: " . $e->getMessage();
}
```

## Next Steps

- Learn [Basic Usage](basic-usage.md) for non-streaming requests
- Explore [Function Calling](function-calling.md) with streaming
- Manage [Conversations](conversations.md) with streaming context