# Function Calling

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides a preview of what will be covered in the complete version.

## Tool Definition

Tools are defined using PHPDoc comments to describe their functionality to AI models.

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
    ->addTool(new WeatherTool())
    ->execute();

echo $response->getMessage()->content;
// Output: Based on the weather data, Paris currently has sunny conditions 
// with a temperature of 22°C and 65% humidity.
```

### Multiple Tools
```php
$response = $chatHandler->createRequest()
    ->addUserMessage('Check the weather in London and find users named John')
    ->addTool(new WeatherTool())
    ->addTool(new DatabaseTool())
    ->execute();

echo $response->getMessage()->content;
```

### Tool with Criteria
```php
use ModelflowAi\DecisionTree\Criteria\FeatureCriteria;

$response = $chatHandler->createRequest()
    ->addUserMessage('Calculate 15% tip on $85.50')
    ->addTool(new CalculatorTool())
    ->withCriteria(FeatureCriteria::TOOLS) // Ensure a model with tool support
    ->execute();
```

## Advanced Tool Usage

### Conditional Tool Execution
```php
class ConditionalTool
{
    /**
     * Send an email notification
     * 
     * Only use this tool if the user explicitly asks to send an email or notification.
     * Do not use for general information requests.
     * 
     * @param string $to Email recipient
     * @param string $subject Email subject
     * @param string $message Email content
     * 
     * @return array{sent: bool, message_id: string|null}
     */
    public function sendEmail(string $to, string $subject, string $message): array
    {
        // Email sending logic
        return ['sent' => true, 'message_id' => 'msg_123'];
    }
}
```

### Tool Error Handling
```php
class RobustTool
{
    /**
     * Get stock price for a symbol
     * 
     * @param string $symbol Stock symbol (e.g., AAPL, GOOGL)
     * 
     * @return array{symbol: string, price: float|null, error: string|null}
     */
    public function getStockPrice(string $symbol): array
    {
        try {
            // API call to get stock price
            $price = $this->stockApi->getPrice($symbol);
            return [
                'symbol' => $symbol,
                'price' => $price,
                'error' => null
            ];
        } catch (\Exception $e) {
            return [
                'symbol' => $symbol,
                'price' => null,
                'error' => 'Could not fetch price: ' . $e->getMessage()
            ];
        }
    }
}
```

## Tool Best Practices

### PHPDoc Guidelines
- **Clear descriptions** - Explain what the tool does
- **Usage guidelines** - When to use the tool
- **Parameter documentation** - Describe each parameter
- **Return type documentation** - Document return structure
- **Examples** - Provide usage examples in comments

### Error Handling
- **Graceful failures** - Return error information instead of throwing
- **Validation** - Validate input parameters
- **Logging** - Log tool usage for debugging
- **Rate limiting** - Respect external API limits

*Complete documentation coming soon...*