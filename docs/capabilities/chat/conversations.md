# Conversations

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides a preview of what will be covered in the complete version.

## Multi-turn Conversations

Chat models maintain context across multiple messages, enabling natural conversations.

### Basic Conversation
```php
$response = $chatHandler->createRequest()
    ->addSystemMessage('You are a helpful programming tutor.')
    ->addUserMessage('What is a PHP array?')
    ->addAssistantMessage('A PHP array is a data structure that can store multiple values...')
    ->addUserMessage('Can you show me an example?')
    ->execute();

echo $response->getMessage()->content;
```

### Building Conversation History
```php
$conversation = [];

// First exchange
$conversation[] = AIChatMessage::createSystemMessage('You are a helpful assistant.');
$conversation[] = AIChatMessage::createUserMessage('Hello!');

$response = $chatHandler->createRequest()
    ->setMessages($conversation)
    ->execute();

$conversation[] = $response->getMessage();

// Second exchange
$conversation[] = AIChatMessage::createUserMessage('What can you help me with?');

$response = $chatHandler->createRequest()
    ->setMessages($conversation)
    ->execute();
```

## Conversation Management

### Session-based Conversations
```php
class ConversationSession
{
    private array $messages = [];
    
    public function __construct(
        private AIChatRequestHandlerInterface $chatHandler,
        private string $systemPrompt = 'You are a helpful assistant.'
    ) {
        $this->messages[] = AIChatMessage::createSystemMessage($this->systemPrompt);
    }
    
    public function sendMessage(string $userMessage): string
    {
        $this->messages[] = AIChatMessage::createUserMessage($userMessage);
        
        $response = $this->chatHandler->createRequest()
            ->setMessages($this->messages)
            ->execute();
            
        $this->messages[] = $response->getMessage();
        
        return $response->getMessage()->content;
    }
    
    public function getHistory(): array
    {
        return $this->messages;
    }
    
    public function reset(): void
    {
        $this->messages = [
            AIChatMessage::createSystemMessage($this->systemPrompt)
        ];
    }
}
```

### Usage
```php
$session = new ConversationSession($chatHandler, 'You are a PHP expert.');

echo $session->sendMessage('What is dependency injection?');
echo $session->sendMessage('Can you show me an example?');
echo $session->sendMessage('How does this relate to containers?');

// Get full conversation history
$history = $session->getHistory();
```

## Advanced Conversation Patterns

### Conversation with Context Limits
```php
class ManagedConversation
{
    private array $messages = [];
    private int $maxMessages;
    
    public function __construct(
        private AIChatRequestHandlerInterface $chatHandler,
        int $maxMessages = 20
    ) {
        $this->maxMessages = $maxMessages;
    }
    
    public function addMessage(string $userMessage): string
    {
        $this->messages[] = AIChatMessage::createUserMessage($userMessage);
        
        // Trim conversation if too long
        if (count($this->messages) > $this->maxMessages) {
            // Keep system message and recent messages
            $systemMessage = $this->messages[0];
            $recentMessages = array_slice($this->messages, -($this->maxMessages - 1));
            $this->messages = array_merge([$systemMessage], $recentMessages);
        }
        
        $response = $this->chatHandler->createRequest()
            ->setMessages($this->messages)
            ->execute();
            
        $this->messages[] = $response->getMessage();
        
        return $response->getMessage()->content;
    }
}
```

### Conversation with Memory
```php
class ConversationWithMemory
{
    private array $currentContext = [];
    private array $longTermMemory = [];
    
    public function __construct(
        private AIChatRequestHandlerInterface $chatHandler
    ) {}
    
    public function chat(string $message): string
    {
        // Add relevant memory to context
        $contextMessages = $this->buildContextWithMemory($message);
        
        $response = $this->chatHandler->createRequest()
            ->setMessages($contextMessages)
            ->execute();
            
        // Store important information in memory
        $this->updateMemory($message, $response->getMessage()->content);
        
        return $response->getMessage()->content;
    }
    
    private function buildContextWithMemory(string $currentMessage): array
    {
        $messages = [
            AIChatMessage::createSystemMessage('You are a helpful assistant with memory.')
        ];
        
        // Add relevant memory
        $relevantMemory = $this->findRelevantMemory($currentMessage);
        if (!empty($relevantMemory)) {
            $memoryContext = "Previous context: " . implode(', ', $relevantMemory);
            $messages[] = AIChatMessage::createSystemMessage($memoryContext);
        }
        
        // Add recent conversation
        $messages = array_merge($messages, $this->currentContext);
        $messages[] = AIChatMessage::createUserMessage($currentMessage);
        
        return $messages;
    }
    
    private function findRelevantMemory(string $message): array
    {
        // Simple keyword matching (in production, use embeddings)
        $relevant = [];
        foreach ($this->longTermMemory as $memory) {
            if (str_contains(strtolower($memory), strtolower($message))) {
                $relevant[] = $memory;
            }
        }
        return array_slice($relevant, 0, 3); // Limit to 3 relevant memories
    }
    
    private function updateMemory(string $userMessage, string $assistantResponse): void
    {
        // Store important exchanges in long-term memory
        if (strlen($userMessage) > 20) { // Only store substantial messages
            $this->longTermMemory[] = "User asked: $userMessage, Assistant: $assistantResponse";
        }
        
        // Update current context
        $this->currentContext[] = AIChatMessage::createUserMessage($userMessage);
        $this->currentContext[] = AIChatMessage::createAssistantMessage($assistantResponse);
        
        // Keep only recent messages in current context
        if (count($this->currentContext) > 10) {
            $this->currentContext = array_slice($this->currentContext, -10);
        }
    }
}
```

## Best Practices

### Context Management
- **Token limits** - Monitor conversation length to stay within model limits
- **Message relevance** - Remove old messages that are no longer relevant
- **System messages** - Use system messages to maintain consistent behavior

### Performance
- **Message chunking** - Split very long conversations
- **Caching** - Cache conversation state in databases or sessions
- **Compression** - Summarize old conversation parts

### User Experience
- **Conversation persistence** - Store conversations across sessions
- **Context indicators** - Show users what context is being used
- **Reset options** - Allow users to start fresh conversations

*Complete documentation coming soon...*