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