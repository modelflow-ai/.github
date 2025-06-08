# Use Cases

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides a preview of what will be covered in the complete version.

## Content Generation

### Blog Writing
```php
class BlogGenerator
{
    public function __construct(
        private AICompletionRequestHandlerInterface $handler
    ) {}
    
    public function generateBlogPost(string $topic, string $audience): string
    {
        $prompt = <<<TEXT
Write a comprehensive blog post about "$topic" for $audience.

Structure:
- Engaging headline
- Introduction with hook
- 3-4 main points with examples
- Practical tips
- Conclusion with call-to-action

Blog post:
TEXT;

        return $this->handler->createRequest($prompt)
            ->withMaxTokens(1000)
            ->withTemperature(0.7)
            ->build()
            ->execute()
            ->getContent();
    }
    
    public function generateSocialMediaPosts(string $content): array
    {
        $prompts = [
            'twitter' => "Convert this blog content into a Twitter thread (max 280 chars per tweet):\n$content\n\nTwitter thread:",
            'linkedin' => "Create a professional LinkedIn post from this content:\n$content\n\nLinkedIn post:",
            'facebook' => "Write an engaging Facebook post based on this content:\n$content\n\nFacebook post:"
        ];
        
        $posts = [];
        foreach ($prompts as $platform => $prompt) {
            $posts[$platform] = $this->handler->createRequest($prompt)
                ->withMaxTokens(200)
                ->build()
                ->execute()
                ->getContent();
        }
        
        return $posts;
    }
}
```

### Email Templates
```php
class EmailGenerator
{
    public function generateWelcomeEmail(string $customerName, string $productName): string
    {
        $prompt = <<<TEXT
Write a warm welcome email for a new customer.

Customer: $customerName
Product: $productName

Include:
- Personal greeting
- Product overview
- Getting started steps
- Support contact info
- Professional but friendly tone

Email:
TEXT;

        return $this->handler->createRequest($prompt)
            ->withMaxTokens(300)
            ->withTemperature(0.6)
            ->build()
            ->execute()
            ->getContent();
    }
    
    public function generateFollowUpEmail(string $context): string
    {
        $prompt = <<<TEXT
Write a professional follow-up email based on this context:
$context

The email should:
- Reference previous interaction
- Provide value or next steps
- Include clear call-to-action
- Maintain professional tone

Email:
TEXT;

        return $this->handler->createRequest($prompt)
            ->withMaxTokens(250)
            ->build()
            ->execute()
            ->getContent();
    }
}
```

## Code Generation

### Function Generation
```php
class CodeGenerator
{
    public function generateFunction(string $description, string $language = 'PHP'): string
    {
        $prompt = <<<TEXT
Write a $language function based on this description:
$description

Requirements:
- Include proper type hints
- Add documentation comments
- Handle edge cases
- Follow best practices
- Include example usage

Code:
TEXT;

        return $this->handler->createRequest($prompt)
            ->withMaxTokens(400)
            ->withTemperature(0.2)
            ->build()
            ->execute()
            ->getContent();
    }
    
    public function generateTests(string $functionCode): string
    {
        $prompt = <<<TEXT
Generate comprehensive unit tests for this function:

$functionCode

Create tests that cover:
- Normal operation
- Edge cases
- Error conditions
- Different input types

Test code:
TEXT;

        return $this->handler->createRequest($prompt)
            ->withMaxTokens(500)
            ->withTemperature(0.1)
            ->build()
            ->execute()
            ->getContent();
    }
}
```

### Documentation Generation
```php
class DocumentationGenerator
{
    public function generateAPIDocumentation(string $code): string
    {
        $prompt = <<<TEXT
Generate comprehensive API documentation for this code:

$code

Include:
- Method descriptions
- Parameter details
- Return value information
- Usage examples
- Error cases

Documentation:
TEXT;

        return $this->handler->createRequest($prompt)
            ->withMaxTokens(600)
            ->build()
            ->execute()
            ->getContent();
    }
}
```

## Data Processing

### Text Transformation
```php
class TextTransformer
{
    public function summarize(string $text, int $maxWords = 100): string
    {
        $prompt = <<<TEXT
Summarize this text in approximately $maxWords words, keeping the main points:

$text

Summary:
TEXT;

        return $this->handler->createRequest($prompt)
            ->withMaxTokens($maxWords * 2)
            ->withTemperature(0.3)
            ->build()
            ->execute()
            ->getContent();
    }
    
    public function extractKeyPoints(string $text): array
    {
        $prompt = <<<TEXT
Extract the key points from this text as a bulleted list:

$text

Key points:
•
TEXT;

        $response = $this->handler->createRequest($prompt)
            ->withMaxTokens(300)
            ->build()
            ->execute()
            ->getContent();
            
        // Parse response into array
        $lines = explode('\n', $response);
        return array_filter(array_map('trim', $lines));
    }
    
    public function translateTone(string $text, string $fromTone, string $toTone): string
    {
        $prompt = <<<TEXT
Rewrite this text to change the tone from "$fromTone" to "$toTone":

Original text:
$text

Rewritten text:
TEXT;

        return $this->handler->createRequest($prompt)
            ->withTemperature(0.5)
            ->build()
            ->execute()
            ->getContent();
    }
}
```

### Data Extraction
```php
class DataExtractor
{
    public function extractContactInfo(string $text): array
    {
        $prompt = <<<TEXT
Extract contact information from this text and format as JSON:

$text

Extract:
- Name
- Email
- Phone
- Company
- Address (if available)

JSON:
TEXT;

        $response = $this->handler->createRequest($prompt)
            ->withMaxTokens(200)
            ->withTemperature(0.1)
            ->build()
            ->execute()
            ->getContent();
            
        return json_decode($response, true) ?? [];
    }
    
    public function classifyText(string $text, array $categories): string
    {
        $categoriesList = implode(', ', $categories);
        
        $prompt = <<<TEXT
Classify this text into one of these categories: $categoriesList

Text: $text

Category:
TEXT;

        return trim($this->handler->createRequest($prompt)
            ->withMaxTokens(10)
            ->withTemperature(0.1)
            ->build()
            ->execute()
            ->getContent());
    }
}
```

## Creative Applications

### Story Generation
```php
class StoryGenerator
{
    public function generateStory(string $genre, array $characters, string $setting): string
    {
        $characterList = implode(', ', $characters);
        
        $prompt = <<<TEXT
Write a short $genre story with these elements:

Characters: $characterList
Setting: $setting

Requirements:
- Engaging opening
- Character development
- Plot progression
- Satisfying conclusion
- Approximately 500 words

Story:
TEXT;

        return $this->handler->createRequest($prompt)
            ->withMaxTokens(700)
            ->withTemperature(0.8)
            ->build()
            ->execute()
            ->getContent();
    }
    
    public function generateCharacterDescription(string $name, string $role): string
    {
        $prompt = <<<TEXT
Create a detailed character description for:

Name: $name
Role: $role

Include:
- Physical appearance
- Personality traits
- Background/history
- Motivations
- Unique characteristics

Character description:
TEXT;

        return $this->handler->createRequest($prompt)
            ->withMaxTokens(300)
            ->withTemperature(0.7)
            ->build()
            ->execute()
            ->getContent();
    }
}
```

## Business Applications

### Report Generation
```php
class ReportGenerator
{
    public function generateExecutiveSummary(array $data): string
    {
        $dataString = json_encode($data, JSON_PRETTY_PRINT);
        
        $prompt = <<<TEXT
Create an executive summary from this business data:

$dataString

Summary should include:
- Key findings
- Important trends
- Recommendations
- Next steps
- Professional tone

Executive Summary:
TEXT;

        return $this->handler->createRequest($prompt)
            ->withMaxTokens(400)
            ->withTemperature(0.4)
            ->build()
            ->execute()
            ->getContent();
    }
    
    public function generateProductDescription(array $productInfo): string
    {
        $info = json_encode($productInfo, JSON_PRETTY_PRINT);
        
        $prompt = <<<TEXT
Write a compelling product description based on this information:

$info

Description should be:
- Customer-focused
- Highlighting benefits
- Persuasive but honest
- SEO-friendly
- 150-200 words

Product description:
TEXT;

        return $this->handler->createRequest($prompt)
            ->withMaxTokens(300)
            ->withTemperature(0.6)
            ->build()
            ->execute()
            ->getContent();
    }
}
```

*Complete documentation coming soon...*