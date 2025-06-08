# Document Q&A System

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides a preview of what will be covered in the complete version.

## RAG-based Q&A System

### Document Indexing
```php
class DocumentIndexer
{
    public function __construct(
        private EmbeddingsRequestHandlerInterface $embeddingsHandler
    ) {}
    
    public function indexDocument(string $content, array $metadata): void
    {
        $this->embeddingsHandler->createStoreRequest()
            ->addContent($content)
            ->withMetadata($metadata)
            ->withKey('documents')
            ->execute();
    }
    
    public function indexPDF(string $pdfPath): void
    {
        $content = $this->extractTextFromPDF($pdfPath);
        $chunks = $this->splitIntoChunks($content);
        
        foreach ($chunks as $index => $chunk) {
            $this->indexDocument($chunk, [
                'source' => $pdfPath,
                'chunk' => $index,
                'type' => 'pdf'
            ]);
        }
    }
}
```

### Question Answering
```php
class DocumentQA
{
    public function answer(string $question): array
    {
        // Find relevant documents
        $relevant = $this->embeddingsHandler
            ->createSimilarityRequest($question, 'documents')
            ->withLimit(3)
            ->execute();
            
        // Prepare context
        $context = [];
        $sources = [];
        
        foreach ($relevant as $doc) {
            $context[] = $doc->getContent();
            $sources[] = $doc->getMetadata();
        }
        
        // Generate answer
        $response = $this->chatHandler->createRequest()
            ->addSystemMessage("Answer based on the provided context.")
            ->addUserMessage($question . "\n\nContext:\n" . implode('\n\n', $context))
            ->execute();
            
        return [
            'answer' => $response->getMessage()->content,
            'sources' => $sources
        ];
    }
}
```

*Complete documentation coming soon...*