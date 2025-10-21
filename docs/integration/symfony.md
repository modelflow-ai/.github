# Symfony Bundle Integration

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides a preview of what will be covered in the complete version.

## Overview

The Symfony bundle provides seamless integration with automatic service registration, configuration management, and dependency injection.

## Installation

```bash
composer require modelflow-ai/symfony-bundle
# Add provider adapters as needed
composer require modelflow-ai/openai-adapter modelflow-ai/ollama-adapter
```

## Configuration

### Basic Setup
```yaml
# config/packages/modelflow_ai.yaml
modelflow_ai:
    providers:
        openai:
            enabled: true
            credentials:
                api_key: '%env(OPENAI_API_KEY)%'
        ollama:
            enabled: true
            url: '%env(OLLAMA_URL)%/'
    
    adapters:
        gpt4o:
            enabled: true
            priority: 10
            criteria:
                - !php/const ModelflowAi\DecisionTree\Criteria\CapabilityCriteria::ADVANCED
        llama3_2:
            enabled: true
            criteria:
                - !php/const ModelflowAi\DecisionTree\Criteria\PrivacyCriteria::HIGH
```

### Embeddings Configuration
```yaml
modelflow_ai:
    embeddings:
        generators:
            default:
                enabled: true
                provider: 'openai'
                model: 'text-embedding-3-small'
                splitter:
                    max_length: 1000
        stores:
            default:
                enabled: true
                dsn: 'qdrant://localhost:6333/documents'
```

### Custom Providers
```yaml
modelflow_ai:
    providers:
        custom:
            my_custom_provider:
                chat_factory: 'App\Service\CustomChatFactory'
                completion_factory: 'App\Service\CustomCompletionFactory'
                image_factory: 'App\Service\CustomImageFactory'
                embeddings_factory: 'App\Service\CustomEmbeddingsFactory'

    adapters:
        my_custom_model:
            enabled: true
            provider: 'my_custom_provider'
            model: 'custom-model-1'
            chat: true

    embeddings:
        generators:
            custom_embeddings:
                enabled: true
                provider: 'my_custom_provider'
                model: 'custom-embedding-model'
```

### Custom Embeddings Factory

Create a custom embeddings factory by implementing the `EmbeddingAdapterFactoryInterface`:

```php
namespace App\Service;

use ModelflowAi\Embeddings\Adapter\EmbeddingAdapterInterface;
use ModelflowAi\Embeddings\Adapter\EmbeddingAdapterFactoryInterface;
use ModelflowAi\Embeddings\Adapter\Request\EmbedRequest;
use ModelflowAi\Embeddings\Adapter\Response\EmbedResponse;
use ModelflowAi\Embeddings\Usage\EmbeddingUsage;

class CustomEmbeddingsFactory implements EmbeddingAdapterFactoryInterface
{
    public function createEmbeddingAdapter(array $options = []): EmbeddingAdapterInterface
    {
        $model = $options['model'] ?? 'default-model';

        return new class($model) implements EmbeddingAdapterInterface {
            public function __construct(private string $model) {}

            public function embed(EmbedRequest $request): EmbedResponse
            {
                $texts = $request->getTexts();
                $vectors = [];

                // Generate embeddings for each text in the batch
                foreach ($texts as $text) {
                    // Your custom embedding logic here
                    $vectors[] = $this->generateEmbedding($text);
                }

                return new EmbedResponse(
                    $vectors,
                    new EmbeddingUsage(
                        count($texts) * 10, // prompt tokens
                        count($texts) * 15  // total tokens
                    )
                );
            }

            private function generateEmbedding(string $text): array
            {
                // Your custom embedding implementation
                // Return an array of floats (the embedding vector)
                return array_fill(0, 1536, 0.1);
            }
        };
    }
}
```

The custom factory will be automatically registered and can be used with the embeddings system.

## Usage in Controllers

### Dependency Injection
```php
use ModelflowAi\Chat\AIChatRequestHandlerInterface;
use ModelflowAi\Embeddings\EmbeddingsRequestHandlerInterface;

class ChatController extends AbstractController
{
    public function __construct(
        private AIChatRequestHandlerInterface $chatHandler,
        private EmbeddingsRequestHandlerInterface $embeddingsHandler
    ) {}
    
    #[Route('/chat', methods: ['POST'])]
    public function chat(Request $request): JsonResponse
    {
        $message = $request->get('message');
        
        $response = $this->chatHandler->createRequest()
            ->addUserMessage($message)
            ->execute();
            
        return $this->json([
            'response' => $response->getMessage()->content
        ]);
    }
}
```

### Service Configuration
```yaml
# config/services.yaml
services:
    _defaults:
        autowire: true
        autoconfigure: true
    
    App\Service\ChatService:
        arguments:
            $chatHandler: '@ModelflowAi\Chat\AIChatRequestHandlerInterface'
```

## Commands

### Chat Command
```php
use Symfony\Component\Console\Command\Command;
use ModelflowAi\Chat\AIChatRequestHandlerInterface;

class AiChatCommand extends Command
{
    public function __construct(
        private AIChatRequestHandlerInterface $chatHandler
    ) {
        parent::__construct();
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $response = $this->chatHandler->createRequest()
            ->addUserMessage('Hello from Symfony!')
            ->execute();
            
        $output->writeln($response->getMessage()->content);
        
        return Command::SUCCESS;
    }
}
```

*Complete documentation coming soon...*