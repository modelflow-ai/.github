# Examples

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides working code examples that demonstrate real usage patterns.

## What Should Be Here

### 1. Example Applications Overview
- Complete, working examples that users can run
- Step-by-step tutorials
- Real-world use cases
- Best practices demonstrated

### 2. Example Categories

#### Quick Examples (Code Snippets)

**Simple Chat Example:**
```php
use ModelflowAi\Chat\AIChatRequestHandler;
use ModelflowAi\OpenaiAdapter\Chat\OpenaiChatAdapter;

$client = OpenAI::client($_ENV['OPENAI_API_KEY']);
$adapter = new OpenaiChatAdapter($client, 'gpt-4o');
$chatHandler = new AIChatRequestHandler(new DecisionTree([new DecisionRule($adapter)]));

$response = $chatHandler->createRequest()
    ->addSystemMessage('You are a helpful assistant.')
    ->addUserMessage('Explain PHP in one sentence.')
    ->execute();

echo $response->getMessage()->content;
```

**Embeddings Search:**
```php
// Store documents
$embeddingsHandler->createStoreRequest()
    ->addContent('PHP is great for web development')
    ->withKey('default')
    ->execute();

// Search
$results = $embeddingsHandler->createSimilarityRequest('web programming', 'default')
    ->execute();
```

#### Complete Applications
- Full working applications
- End-to-end implementations
- Production-ready patterns

#### Tutorials
- Step-by-step learning guides
- Progressive complexity
- Hands-on exercises

### 3. Featured Examples

#### Chatbot Application
- Customer support bot
- Conversation management
- Multi-provider support
- Streaming responses

#### Document Q&A System
- RAG implementation
- Document indexing with embeddings
- Semantic search
- Question answering

#### Image Generation App
- Text-to-image interface
- Image analysis and description
- Batch processing
- Content moderation

#### Embeddings Search Engine
- Vector database integration
- Similarity search
- Content recommendation
- Duplicate detection

## Example Pages to Create

- **chatbot.md** - Complete chatbot implementation
- **document-qa.md** - RAG system with embeddings
- **image-generation.md** - Image creation application
- **embeddings-search.md** - Vector search system

## Content Guidelines

- **Working code** - All examples must run
- **Complete setup** - Include all dependencies and configuration
- **Explained steps** - Clear explanations of what each part does
- **Best practices** - Show proper error handling, logging, etc.
- **Multiple approaches** - Show different ways to solve the same problem
- **Production ready** - Include deployment and scaling considerations