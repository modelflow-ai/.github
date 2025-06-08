# Quick Start

Get up and running with Modelflow AI in just 5 minutes! This guide shows practical examples for each capability with different providers.

## Prerequisites

You only need:
- PHP 8.2+
- Composer
- At least one API key (or Ollama installed locally)

## 🗨️ Chat Examples

Chat is the most popular AI capability. Here's how to have a conversation with different providers.

**Required packages:**
```bash
composer require modelflow-ai/chat modelflow-ai/openai-adapter
# For Anthropic examples: modelflow-ai/anthropic-adapter
# For Ollama examples: modelflow-ai/ollama-adapter
```

### OpenAI GPT-4o

```php
use ModelflowAi\Chat\AIChatRequestHandler;
use ModelflowAi\OpenaiAdapter\Chat\OpenaiChatAdapter;
use ModelflowAi\DecisionTree\DecisionTree;
use ModelflowAi\DecisionTree\DecisionRule;

// Simple setup
$client = OpenAI::client($_ENV['OPENAI_API_KEY']);
$adapter = new OpenaiChatAdapter($client, 'gpt-4o');
$chatHandler = new AIChatRequestHandler(new DecisionTree([new DecisionRule($adapter)]));

// Have a conversation
$response = $chatHandler->createRequest()
    ->addSystemMessage('You are a helpful assistant.')
    ->addUserMessage('What are the benefits of PHP for web development?')
    ->execute();

echo $response->getMessage()->content;
// Output: PHP offers several benefits for web development including ease of learning,
// extensive documentation, large community support, excellent database integration...
```

### Anthropic Claude

Same code structure, different provider:

```php
use ModelflowAi\Anthropic\Anthropic;
use ModelflowAi\AnthropicAdapter\Chat\AnthropicChatAdapter;
use ModelflowAi\Chat\AIChatRequestHandler;
use ModelflowAi\DecisionTree\DecisionRule;
use ModelflowAi\DecisionTree\DecisionTree;

$client = Anthropic::client($_ENV['ANTHROPIC_API_KEY']);
$adapter = new AnthropicChatAdapter($client, 'claude-3-5-sonnet-20241022');
$chatHandler = new AIChatRequestHandler(new DecisionTree([new DecisionRule($adapter)]));

$response = $chatHandler->createRequest()
    ->addSystemMessage('You are a helpful assistant.')
    ->addUserMessage('What are the benefits of PHP for web development?')
    ->execute();

echo $response->getMessage()->content;
```

### Local with Ollama

Run models locally for privacy:

```php
use ModelflowAi\Chat\AIChatRequestHandler;
use ModelflowAi\DecisionTree\DecisionRule;
use ModelflowAi\DecisionTree\DecisionTree;
use ModelflowAi\Ollama\Ollama;
use ModelflowAi\OllamaAdapter\Chat\OllamaChatAdapter;

$client = Ollama::client(); // Defaults to http://localhost:11434
$adapter = new OllamaChatAdapter($client, 'llama3.2');
$chatHandler = new AIChatRequestHandler(new DecisionTree([new DecisionRule($adapter)]));

$response = $chatHandler->createRequest()
    ->addUserMessage('Explain quantum computing in one sentence.')
    ->execute();

echo $response->getMessage()->content;
// Output: Quantum computing uses quantum bits (qubits) that can exist in multiple 
// states simultaneously to perform certain calculations exponentially faster than classical computers.
```

### Streaming Responses

Get responses as they're generated:

```php
$response = $chatHandler->createStreamedRequest()
    ->addUserMessage('Write a haiku about programming')
    ->execute();

foreach ($response->getMessageStream() as $index => $message) {
    if (0 === $index) {
        echo $message->role->value . ': ';
    }
    echo $message->content;
}
// Output: 
// assistant: Code flows like water
// Bugs surface, then disappear  
// Logic brings order
```

## 📝 Completion Examples

Text completion is perfect for generating content, code, or finishing prompts.

**Required packages:**
```bash
composer require modelflow-ai/completion modelflow-ai/ollama-adapter
```

### Ollama Completion

```php
use ModelflowAi\Completion\AICompletionRequestHandler;
use ModelflowAi\DecisionTree\DecisionRule;
use ModelflowAi\DecisionTree\DecisionTree;
use ModelflowAi\Ollama\Ollama;
use ModelflowAi\OllamaAdapter\Completion\OllamaCompletionAdapter;

$client = Ollama::client();
$adapter = new OllamaCompletionAdapter($client, 'llama3.2');
$completionHandler = new AICompletionRequestHandler(new DecisionTree([new DecisionRule($adapter)]));

// Complete a prompt
$response = $completionHandler->createRequest('The three most important programming principles are:')
    ->build()
    ->execute();

echo $response->getContent();
// Output: 1. DRY (Don't Repeat Yourself) - Avoid code duplication
// 2. KISS (Keep It Simple, Stupid) - Write clear, simple code
// 3. SOLID - Five principles for maintainable object-oriented design
```

## 🧠 Embeddings Examples

Convert text to vectors for semantic search and similarity matching.

**Required packages:**
```bash
composer require modelflow-ai/embeddings modelflow-ai/openai-adapter
# For Mistral examples: modelflow-ai/mistral-adapter
# Optional: modelflow-ai/qdrant-embeddings-store (for vector storage)
```

### Semantic Search with OpenAI

```php
use ModelflowAi\Embeddings\EmbeddingsRequestHandler;
use ModelflowAi\Embeddings\Model\Embedding;
use ModelflowAi\Embeddings\Handler\EmbeddingsStoreHandler;
use ModelflowAi\Embeddings\Handler\EmbeddingsSimilarityHandler;
use ModelflowAi\Embeddings\Generator\EmbeddingGenerator;
use ModelflowAi\Embeddings\Splitter\EmbeddingSplitter;
use ModelflowAi\Embeddings\Formatter\EmbeddingFormatter;
use ModelflowAi\Embeddings\Store\Filesystem\FilesystemEmbeddingsStore;
use ModelflowAi\OpenaiAdapter\Embeddings\OpenaiEmbeddingAdapter;

// Setup (in production, this would be done once)
$adapter = new OpenaiEmbeddingAdapter(
    OpenAI::client($_ENV['OPENAI_API_KEY']), 
    'text-embedding-3-small'
);
$store = new FilesystemEmbeddingsStore('/tmp/embeddings.txt');

// Quick setup for demo
$generator = new EmbeddingGenerator(
    new EmbeddingSplitter(500),
    new EmbeddingFormatter()
);

$storeHandler = new EmbeddingsStoreHandler(
    ['default' => $generator],
    ['default' => $store],
    ['default' => $adapter],
    [Embedding::class => 'default']
);

$similarityHandler = new EmbeddingsSimilarityHandler(
    ['default' => $store],
    ['default' => $adapter]
);

$embeddingsHandler = new EmbeddingsRequestHandler($storeHandler, $similarityHandler);

// Store some documents
$docs = [
    new Embedding('PHP is a server-side scripting language.', 'doc1'),
    new Embedding('JavaScript runs in the browser.', 'doc2'),
    new Embedding('Python is great for data science.', 'doc3'),
    new Embedding('PHP frameworks include Laravel and Symfony.', 'doc4')
];

$embeddingsHandler->createStoreRequest(...$docs)->execute();

// Search for similar content
$results = $embeddingsHandler->createSimilarityRequest('Tell me about PHP', 'default')
    ->withLimit(2)
    ->execute();

foreach ($results->getEmbeddings() as $embedding) {
    echo "- " . $embedding->getContent() . "\n";
}
// Output:
// - PHP is a server-side scripting language.
// - PHP frameworks include Laravel and Symfony.
```

### Using Mistral Embeddings

```php
use ModelflowAi\Mistral\Mistral;
use ModelflowAi\MistralAdapter\Embeddings\MistralEmbeddingAdapter;

// Just swap the adapter!
$adapter = new MistralEmbeddingAdapter(
    Mistral::client($_ENV['MISTRAL_API_KEY']), 
    'mistral-embed'
);
// Rest of the code remains the same...
```

## 🖼️ Image Generation

Create images from text descriptions.

**Required packages:**
```bash
composer require modelflow-ai/image modelflow-ai/openai-adapter
```

### DALL-E 3 with OpenAI

```php
use ModelflowAi\Image\AIImageRequestHandler;
use ModelflowAi\OpenaiAdapter\Image\OpenAIImageGenerationAdapter;
use ModelflowAi\Image\Request\Value\ImageFormat;

$client = OpenAI::client($_ENV['OPENAI_API_KEY']);
$adapter = new OpenAIImageGenerationAdapter(HttpClient::create(), $client, 'dall-e-3');
$middleware = new HandleMiddleware(new DecisionTree([new DecisionRule($adapter)]));
$imageHandler = new AIImageRequestHandler($middleware);

// Generate an image
$response = $imageHandler->createRequest()
    ->textToImage('A cute robot learning PHP, digital art style')
    ->imageFormat(ImageFormat::PNG)
    ->asStream()
    ->build()
    ->execute();

// Save the image stream
file_put_contents('robot-learning-php.png', stream_get_contents($response->stream()));
echo "Image saved as robot-learning-php.png";

// Or get as base64
$base64Response = $imageHandler->createRequest()
    ->textToImage('A cute robot learning PHP, digital art style')
    ->imageFormat(ImageFormat::PNG)
    ->asBase64()
    ->build()
    ->execute();

echo "Base64 image: " . substr($base64Response->base64(), 0, 50) . "...";
```

## 🎯 Symfony Bundle

If you're using Symfony, everything becomes even easier with dependency injection. This section includes examples from our live coding session at SymfonyCon 2024!

**Required packages:**
```bash
composer require modelflow-ai/symfony-bundle modelflow-ai/chat
# Plus any provider adapters you want to use:
composer require modelflow-ai/openai-adapter modelflow-ai/anthropic-adapter modelflow-ai/ollama-adapter
```

### Configuration

First, configure your providers in `config/packages/modelflow_ai.yaml`:

```yaml
modelflow_ai:
    providers:
        openai:
            enabled: true
            credentials:
                api_key: '%env(OPENAI_API_KEY)%'
        anthropic:
            enabled: true
            credentials:
                api_key: '%env(ANTHROPIC_API_KEY)%'
        ollama:
            enabled: true
            url: '%env(OLLAMA_URL)%'
    
    adapters:
        gpt4o:
            enabled: true
        claude_3_5_sonnet:
            enabled: true
        llama3_2:
            enabled: true
```

### Using in Controllers

```php
namespace App\Controller;

use ModelflowAi\Chat\AIChatRequestHandlerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ChatController extends AbstractController
{
    #[Route('/api/chat', methods: ['POST'])]
    public function chat(
        AIChatRequestHandlerInterface $chatHandler,
        Request $request
    ): JsonResponse {
        $message = $request->toArray()['message'] ?? '';
        
        $response = $chatHandler->createRequest()
            ->addUserMessage($message)
            ->execute();
        
        return $this->json([
            'response' => $response->getMessage()->content
        ]);
    }
}
```

### Using in Services

```php
namespace App\Service;

use ModelflowAi\Chat\AIChatRequestHandlerInterface;
use ModelflowAi\Embeddings\EmbeddingsRequestHandlerInterface;

class AIService
{
    public function __construct(
        private AIChatRequestHandlerInterface $chatHandler,
        private EmbeddingsRequestHandlerInterface $embeddingsHandler
    ) {}
    
    public function summarizeText(string $text): string
    {
        $response = $this->chatHandler->createRequest()
            ->addSystemMessage('You are a helpful summarizer. Be concise.')
            ->addUserMessage("Summarize this text: $text")
            ->execute();
            
        return $response->getMessage()->content;
    }
    
    public function findSimilar(string $query): array
    {
        return $this->embeddingsHandler
            ->createSimilarityRequest($query, 'default')
            ->withLimit(5)
            ->execute()
            ->getEmbeddings();
    }
    
    public function storeKnowledgeBase(array $documents): void
    {
        $embeddings = [];
        foreach ($documents as $id => $content) {
            $embeddings[] = new \ModelflowAi\Embeddings\Model\Embedding($content, (string) $id);
        }
        
        $this->embeddingsHandler->createStoreRequest(...$embeddings)->execute();
    }
    
    public function searchKnowledge(string $question, string $storeKey = 'default'): string
    {
        // Find relevant documents
        $similarDocs = $this->embeddingsHandler
            ->createSimilarityRequest($question, $storeKey)
            ->withLimit(3)
            ->execute()
            ->getEmbeddings();
        
        // Build context from similar documents
        $context = '';
        foreach ($similarDocs as $doc) {
            $context .= $doc->getContent() . "\n\n";
        }
        
        // Answer question using context
        $response = $this->chatHandler->createRequest()
            ->addSystemMessage('Answer the question based on the provided context. If the context doesn\'t contain enough information, say so.')
            ->addUserMessage("Context:\n$context\n\nQuestion: $question")
            ->execute();
            
        return $response->getMessage()->content;
    }
}
```

### Console Commands

Build an interactive chatbot like we did at SymfonyCon 2024:

```php
namespace App\Command;

use ModelflowAi\Chat\AIChatRequestHandlerInterface;
use ModelflowAi\Chat\Request\Message\AIChatMessage;
use ModelflowAi\Chat\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\DecisionTree\Criteria\ProviderCriteria;
use ModelflowAi\DecisionTree\Criteria\ModelCriteria;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:chat',
    description: 'Interactive AI chat'
)]
class ChatCommand extends Command
{
    /** @var AIChatMessage[] */
    private array $messages = [];
    
    public function __construct(
        private AIChatRequestHandlerInterface $chatHandler
    ) {
        parent::__construct();
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('AI Chat - Type "exit" to quit');
        $io->info('Using: GPT-4o by default, or add --model=llama3.2 for local');
        
        // Optional: Select model via command option
        $model = $input->getOption('model') ?? 'gpt4o';
        $criteria = match($model) {
            'llama3.2' => [ProviderCriteria::OLLAMA, ModelCriteria::LLAMA3_2],
            default => [ProviderCriteria::OPENAI, ModelCriteria::GPT4O],
        };
        
        while (true) {
            $question = $io->ask('You');
            
            if ($question === 'exit') {
                $io->success('Goodbye!');
                break;
            }
            
            // Add user message to history
            $this->messages[] = AIChatMessage::create(
                AIChatMessageRoleEnum::USER, 
                $question
            );
            
            // Stream the response
            $io->write('<info>AI:</info> ');
            
            $request = $this->chatHandler->createRequest()
                ->withCriteria(...$criteria);
                
            foreach ($this->messages as $message) {
                $request->addMessage($message);
            }
            
            $streamResponse = $request->executeStreamed();
            $fullResponse = '';
            
            foreach ($streamResponse->getMessageStream() as $index => $message) {
                if (0 === $index) {
                    // First chunk, role already shown
                }
                $content = $message->content;
                $io->write($content);
                $fullResponse .= $content;
            }
            
            $io->newLine();
            
            // Add AI response to history
            $this->messages[] = AIChatMessage::create(
                AIChatMessageRoleEnum::ASSISTANT,
                $fullResponse
            );
        }
        
        return Command::SUCCESS;
    }
    
    protected function configure(): void
    {
        $this->addOption('model', 'm', InputOption::VALUE_REQUIRED, 'AI model to use');
    }
}
```

Use it:
```bash
# Chat with GPT-4o
php bin/console app:chat

# Chat with local Llama model
php bin/console app:chat --model=llama3.2

# Interactive session example:
You: What is Symfony?
AI: Symfony is a popular PHP web application framework...
You: Tell me about its components
AI: Symfony components are reusable PHP libraries...
You: exit
Goodbye!
```

## What's Next?

Now that you've seen Modelflow AI in action:

- **[Installation Guide](./installation)** - Set up your project properly
- **[Architecture](./architecture)** - Understand how it all works
- **[Chat Documentation](/capabilities/chat/)** - Deep dive into conversations
- **[Provider Comparison](/providers/)** - Choose the right AI provider

## Tips for Success

1. **Start Simple** - Begin with basic chat, then add features
2. **Use Environment Variables** - Never hardcode API keys
3. **Try Local Models** - Ollama is great for development and privacy
4. **Leverage the Bundle** - If using Symfony, the bundle saves tons of setup
5. **Switch Providers Easily** - The same code works with all providers

Happy coding with Modelflow AI! 🚀
