# Conversations

Multi-turn conversations allow AI models to maintain context across multiple exchanges, enabling natural dialogue and complex interactions.

## Basic Usage

```php
// Multi-turn conversation
$response = $chatHandler->createRequest()
    ->addSystemMessage('You are a helpful programming tutor.')
    ->addUserMessage('What is a PHP array?')
    ->addAssistantMessage('A PHP array is a data structure that can store multiple values...')
    ->addUserMessage('Can you show me an example?')
    ->execute();

echo $response->getMessage()->content;
```

## Conversation Management

### Simple Session

```php
$response = $chatHandler->createRequest(...$messages)
    ->addSystemMessage('You are a helpful assistant.')
    ->addUserMessage('Hello!')
    ->execute();

$messages = $response->getRequest()->getMessages();
$messages[] = $response->getMessage();

// Continue conversation
$response = $chatHandler->createRequest(...$messages)
    ->addUserMessage('What can you help me with?')
    ->execute();

$messages = $response->getRequest()->getMessages();
```

### Conversation Class

```php
class ConversationSession
{
    private array $messages = [];
    
    public function __construct(
        private AIChatRequestHandlerInterface $chatHandler,
        string $systemPrompt = 'You are a helpful assistant.'
    ) {
        $this->messages[] = AIChatMessage::create(
            AIChatMessageRoleEnum::SYSTEM, 
            $systemPrompt
        );
    }
    
    public function sendMessage(string $userMessage): string
    {
        $response = $this->chatHandler->createRequest(...$this->messages)
            ->addUserMessage($userMessage)
            ->execute();
        
        $result = $response->getMessage()->content;
        $this->messages = $response->getRequest()->getMessages();
        
        return $result;
    }
    
    public function reset(): void
    {
        $this->messages = [$this->messages[0]]; // Keep only system message
    }
}

// Usage
$session = new ConversationSession($chatHandler, 'You are a PHP expert.');
echo $session->sendMessage('What is dependency injection?');
echo $session->sendMessage('Can you show me an example?');
```

## Context Management

### Token Limits

```php
class ManagedConversation
{
    private array $messages = [];
    private int $maxMessages;
    
    public function __construct(
        private AIChatRequestHandlerInterface $chatHandler,
        int $maxMessages = 10
    ) {
        $this->maxMessages = $maxMessages;
    }
    
    public function addMessage(string $userMessage): string
    {   
        // Keep only recent messages
        if (count($this->messages) >= $this->maxMessages) {
            array_shift($this->messages); // Remove oldest
        }
        
        $request = $this->chatHandler->createRequest(...$this->messages)
            ->addUserMessage($userMessage);
        
        $response = $request->execute();
        $this->messages[] = $response->getMessage();
        
        $result = $response->getMessage()->content;
        $this->messages = $response->getRequest()->getMessages();

        return $result;
    }
}
```

### Persistent Storage

```php
class PersistentConversation
{
    public function __construct(
        private AIChatRequestHandlerInterface $chatHandler,
        private PDO $pdo,
        private string $conversationId
    ) {}
    
    public function sendMessage(string $userMessage): string
    {
        // Get conversation history
        $messages = $this->getMessages(10); // Last 10 messages
        
        $response = $this->chatHandler->createRequest(...$messages)
            ->addUserMessage($userMessage)
            ->execute();
        
        // Save all messages from the final request
        $this->saveMessages($response->getRequest()->getMessages());
        
        return $response->getMessage()->content;
    }
    
    private function saveMessages(array $messages): void
    {
        // Clear existing messages for this conversation
        $stmt = $this->pdo->prepare("DELETE FROM messages WHERE conversation_id = ?");
        $stmt->execute([$this->conversationId]);
        
        // Save all messages
        $stmt = $this->pdo->prepare("
            INSERT INTO messages (conversation_id, role, content, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        
        foreach ($messages as $message) {
            $stmt->execute([
                $this->conversationId, 
                $message->role->value, 
                $message->content
            ]);
        }
    }
    
    private function getMessages(int $limit = 10): array
    {
        $stmt = $this->pdo->prepare("
            SELECT role, content FROM messages
            WHERE conversation_id = ?
            ORDER BY created_at ASC
            LIMIT ?
        ");
        
        $stmt->execute([$this->conversationId, $limit]);
        
        $messages = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $messages[] = AIChatMessage::create(
                AIChatMessageRoleEnum::from($row['role']),
                $row['content']
            );
        }
        
        return $messages;
    }
}
```


## Best Practices

- **Context Management**: Keep conversations under 10 messages for optimal performance
- **Token Limits**: Monitor message length to avoid exceeding model limits
- **System Messages**: Use consistent system prompts for reliable behavior
- **Storage**: Persist conversations to database or cache for session continuity
- **Security**: Encrypt stored conversations and implement proper access controls

## Next Steps

- Learn [Basic Usage](basic-usage.md) for foundational concepts
- Implement [Function Calling](function-calling.md) in conversations
- Use [Streaming](streaming.md) for real-time conversations
