# Prompt Engineering

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides a preview of what will be covered in the complete version.

## Effective Prompt Design

### Clear and Specific Prompts
```php
// ❌ Vague prompt
$response = $completionHandler->createRequest('Write about dogs')
    ->build()->execute();

// ✅ Specific prompt
$response = $completionHandler->createRequest(
    'Write a 100-word informative paragraph about Golden Retrievers, focusing on their temperament and suitability as family pets:'
)->build()->execute();
```

### Context and Examples
```php
$prompt = <<<TEXT
Write product descriptions in this style:

Example:
Product: Wireless Headphones
Description: Experience crystal-clear audio with our premium wireless headphones. Featuring 30-hour battery life and noise cancellation technology, these headphones deliver exceptional sound quality for music lovers and professionals alike.

Now write a description for:
Product: Bluetooth Speaker
Description:
TEXT;

$response = $completionHandler->createRequest($prompt)
    ->withMaxTokens(100)
    ->build()
    ->execute();
```

## Prompt Patterns

### Instruction-Following
```php
$prompt = <<<TEXT
Instructions: Convert the following text to a formal business tone.

Original: "Hey there! Thanks for reaching out. We'll get back to you soon!"

Formal version:
TEXT;

$response = $completionHandler->createRequest($prompt)
    ->withTemperature(0.3)
    ->build()
    ->execute();
```

### Chain of Thought
```php
$prompt = <<<TEXT
Problem: A store sells apples for \$2 per pound and oranges for \$3 per pound. If someone buys 4 pounds of apples and 2 pounds of oranges, what's the total cost?

Let me solve this step by step:
1. Cost of apples:
TEXT;

$response = $completionHandler->createRequest($prompt)
    ->withMaxTokens(150)
    ->build()
    ->execute();
```

### Template Completion
```php
$prompt = <<<TEXT
Complete this email template:

Subject: Welcome to [COMPANY NAME]

Dear [CUSTOMER NAME],

Thank you for joining [COMPANY NAME]. We're excited to have you as part of our community.

Here's what you can expect:
TEXT;

$response = $completionHandler->createRequest($prompt)
    ->withMaxTokens(200)
    ->build()
    ->execute();
```

## Parameter Optimization

### Temperature Control
```php
// Creative writing (high temperature)
$creative = $completionHandler->createRequest('Write a creative story beginning:')
    ->withTemperature(0.9)
    ->build()
    ->execute();

// Technical documentation (low temperature)
$technical = $completionHandler->createRequest('Explain how HTTP works:')
    ->withTemperature(0.1)
    ->build()
    ->execute();

// Balanced response (medium temperature)
$balanced = $completionHandler->createRequest('Describe the benefits of exercise:')
    ->withTemperature(0.7)
    ->build()
    ->execute();
```

### Token Management
```php
// Short responses
$summary = $completionHandler->createRequest('Summarize machine learning in one sentence:')
    ->withMaxTokens(30)
    ->build()
    ->execute();

// Detailed responses
$detailed = $completionHandler->createRequest('Explain machine learning concepts:')
    ->withMaxTokens(500)
    ->build()
    ->execute();
```

### Stop Sequences
```php
// Stop at natural breakpoints
$response = $completionHandler->createRequest('List the top 5 programming languages:')
    ->withStopSequences(['\n\n', 'Conclusion:', 'Summary:'])
    ->build()
    ->execute();

// Stop at specific markers
$response = $completionHandler->createRequest('Generate FAQ entries:')
    ->withStopSequences(['---', 'END'])
    ->build()
    ->execute();
```

## Advanced Techniques

### Multi-step Prompting
```php
class MultiStepCompletion
{
    public function __construct(
        private AICompletionRequestHandlerInterface $handler
    ) {}
    
    public function generateBlogPost(string $topic): string
    {
        // Step 1: Generate outline
        $outline = $this->handler->createRequest("Create a blog post outline for: $topic")
            ->withMaxTokens(150)
            ->build()
            ->execute()
            ->getContent();
            
        // Step 2: Generate introduction
        $intro = $this->handler->createRequest("Write an engaging introduction for a blog post with this outline:\n$outline")
            ->withMaxTokens(200)
            ->build()
            ->execute()
            ->getContent();
            
        // Step 3: Generate main content
        $content = $this->handler->createRequest("Write the main content for this blog post:\nOutline: $outline\nIntroduction: $intro")
            ->withMaxTokens(800)
            ->build()
            ->execute()
            ->getContent();
            
        return $intro . "\n\n" . $content;
    }
}
```

### Conditional Logic
```php
$userInput = "I want to learn programming";

$prompt = <<<TEXT
Analyze this user request: "$userInput"

If the user wants to learn programming:
- Recommend starting languages
- Suggest learning resources
- Provide next steps

If the user wants something else:
- Acknowledge their request
- Offer relevant help

Response:
TEXT;

$response = $completionHandler->createRequest($prompt)
    ->build()
    ->execute();
```

## Best Practices

### Prompt Structure
1. **Context** - Provide necessary background
2. **Instructions** - Clear, specific directions
3. **Examples** - Show desired output format
4. **Constraints** - Length, style, format requirements

### Common Pitfalls
- **Ambiguous instructions** - Be specific about what you want
- **Too much context** - Keep relevant information concise
- **Inconsistent examples** - Ensure examples match desired output
- **Wrong temperature** - Use low for factual, high for creative

### Testing and Iteration
```php
class PromptTester
{
    public function testPrompt(string $prompt, array $variations): array
    {
        $results = [];
        
        foreach ($variations as $name => $params) {
            $response = $this->handler->createRequest($prompt)
                ->withTemperature($params['temperature'])
                ->withMaxTokens($params['max_tokens'])
                ->build()
                ->execute();
                
            $results[$name] = $response->getContent();
        }
        
        return $results;
    }
}

// Usage
$tester = new PromptTester($completionHandler);
$results = $tester->testPrompt('Explain quantum computing:', [
    'creative' => ['temperature' => 0.9, 'max_tokens' => 200],
    'technical' => ['temperature' => 0.1, 'max_tokens' => 200],
    'balanced' => ['temperature' => 0.5, 'max_tokens' => 200]
]);
```

*Complete documentation coming soon...*