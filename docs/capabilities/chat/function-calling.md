# Function Calling

Function calling (also known as tool use) allows AI models to interact with external systems, APIs, and databases by calling PHP methods. Modelflow AI provides a robust system for defining, executing, and managing function calls within chat conversations.

## Overview

Function calling enables AI models to:
- Access real-time data (weather, stock prices, news)
- Interact with databases and APIs
- Perform calculations and data processing
- Execute actions (send emails, create tickets, update records)
- Integrate with external services

## Tool Definition

Tools in Modelflow AI are PHP classes with methods that can be called by the AI. The system uses PHPDoc comments to generate function schemas that AI models understand.

### Simple Tool Example
```php
class WeatherTool
{
    /**
     * Get current weather for a city
     * 
     * @param string $city The city to get weather for
     * 
     * @return array{temperature: int, condition: string, humidity: int}
     */
    public function getCurrentWeather(string $city): array
    {
        // In a real implementation, you'd call a weather API
        return [
            'temperature' => 22,
            'condition' => 'sunny',
            'humidity' => 65
        ];
    }
}
```

### Complex Tool Example
```php
class DatabaseTool
{
    /**
     * Search for users in the database
     * 
     * Use this tool when the user asks to find, search, or look up user information.
     * 
     * @param string $query Search query for user name or email
     * @param int $limit Maximum number of results to return (default: 10)
     * @param string[] $fields Fields to include in results (name, email, created_at)
     * 
     * @return array{users: array, total: int, query_time: float}
     */
    public function searchUsers(string $query, int $limit = 10, array $fields = ['name', 'email']): array
    {
        // Database search implementation
        return [
            'users' => [
                ['name' => 'John Doe', 'email' => 'john@example.com'],
                ['name' => 'Jane Smith', 'email' => 'jane@example.com']
            ],
            'total' => 2,
            'query_time' => 0.045
        ];
    }
}
```

## Using Tools in Chat

### Single Tool
```php
$response = $chatHandler->createRequest()
    ->addSystemMessage('You are a helpful assistant with access to weather data.')
    ->addUserMessage('What is the weather like in Paris?')
    ->tool('get_current_weather', new WeatherTool(), 'getCurrentWeather')
    ->execute();

echo $response->getMessage()->content;
// Output: Based on the weather data, Paris currently has sunny conditions 
// with a temperature of 22°C and 65% humidity.
```

### Multiple Tools
```php
$response = $chatHandler->createRequest()
    ->addUserMessage('Check the weather in London and find users named John')
    ->tool('get_current_weather', new WeatherTool(), 'getCurrentWeather')
    ->tool('search_users', new DatabaseTool(), 'searchUsers')
    ->execute();

echo $response->getMessage()->content;
```

## Best Practices

### PHPDoc Guidelines
- **Clear Descriptions**: Write concise but complete descriptions of what the tool does
- **Usage Instructions**: Include when and why to use the tool in the description
- **Parameter Documentation**: Document each parameter with type and constraints
- **Return Type Documentation**: Clearly document the structure of return values
- **Examples**: Include usage examples in the PHPDoc comments

### Error Handling
- **Graceful Failures**: Return error information instead of throwing exceptions
- **Input Validation**: Validate all input parameters before processing
- **Detailed Logging**: Log all tool usage for debugging and monitoring
- **Rate Limiting**: Implement and respect rate limits for external APIs
- **Timeout Handling**: Set appropriate timeouts for external calls

### Security Considerations
- **Permission Checks**: Implement proper authorization for sensitive operations
- **Input Sanitization**: Sanitize all user inputs to prevent injection attacks
- **API Key Management**: Never expose API keys in tool responses
- **Audit Logging**: Log all tool executions for security auditing
- **Data Privacy**: Ensure tools comply with data protection regulations

### Performance Optimization
- **Caching**: Cache frequently requested data when appropriate
- **Batch Operations**: Support batch operations to reduce API calls
- **Async Execution**: Use async operations for I/O-bound tasks
- **Connection Pooling**: Reuse connections for database and API calls
- **Result Pagination**: Implement pagination for large result sets

## Next Steps

- Explore [Conversations](conversations.md) for multi-turn tool interactions
- Learn about [Streaming](streaming.md) with tool calls
- Review [Basic Usage](basic-usage.md) for foundational concepts
