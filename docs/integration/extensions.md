# Extensions

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides a preview of what will be covered in the complete version.

## Experts Package

### Installation
```bash
composer require modelflow-ai/experts
```

### Creating Experts
```php
use ModelflowAi\Experts\Expert;

$expert = new Expert(
    'PHP Developer',
    'You are an expert PHP developer with 10+ years experience',
    $chatHandler
);

$response = $expert->ask('How do I optimize database queries?');
```

## Tools Package

### Installation
```bash
composer require modelflow-ai/tools
```

### Google Search Tool
```php
use ModelflowAi\Tools\GoogleSearch\GoogleSearchTool;

$searchTool = new GoogleSearchTool(
    $httpClient,
    $_ENV['GOOGLE_SEARCH_ENGINE_ID'],
    $_ENV['GOOGLE_SEARCH_API_KEY']
);

$results = $searchTool->search('PHP best practices');
```

*Complete documentation coming soon...*
