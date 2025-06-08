# RAG (Retrieval-Augmented Generation)

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides a preview of what will be covered in the complete version.

## RAG Implementation

### Basic RAG System
```php
class RAGSystem
{
    public function __construct(
        private EmbeddingsRequestHandlerInterface $embeddingsHandler,
        private AIChatRequestHandlerInterface $chatHandler
    ) {}
    
    public function ask(string $question): string
    {
        // 1. Find relevant documents
        $relevant = $this->embeddingsHandler
            ->createSimilarityRequest($question, 'knowledge_base')
            ->withLimit(3)
            ->execute();
            
        // 2. Prepare context
        $context = [];
        foreach ($relevant as $doc) {
            $context[] = $doc->getContent();
        }
        $contextText = implode('\n\n', $context);
        
        // 3. Generate answer with context
        $response = $this->chatHandler->createRequest()
            ->addSystemMessage("Answer based on this context:\n\n$contextText")
            ->addUserMessage($question)
            ->execute();
            
        return $response->getMessage()->content;
    }
}
```

### Advanced RAG
```php
class AdvancedRAGSystem
{
    public function askWithSources(string $question): array
    {
        $relevant = $this->embeddingsHandler
            ->createSimilarityRequest($question, 'knowledge_base')
            ->withLimit(5)
            ->execute();
            
        $context = [];
        $sources = [];
        
        foreach ($relevant as $doc) {
            $context[] = $doc->getContent();
            $sources[] = $doc->getMetadata();
        }
        
        $response = $this->chatHandler->createRequest()
            ->addSystemMessage("Answer based on the provided context. Cite sources when possible.")
            ->addUserMessage($question . "\n\nContext:\n" . implode('\n\n', $context))
            ->execute();
            
        return [
            'answer' => $response->getMessage()->content,
            'sources' => $sources,
            'confidence' => $this->calculateConfidence($relevant)
        ];
    }
}
```

*Complete documentation coming soon...*