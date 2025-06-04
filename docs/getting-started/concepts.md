# Core Concepts

> **Placeholder Content** - This should explain the fundamental concepts users need to understand.

## What Should Be Here

### 1. Key Concepts

#### Providers
- What are AI providers (OpenAI, Anthropic, etc.)
- How Modelflow AI abstracts them
- Provider capabilities comparison

#### Adapters
- How adapters work
- Provider-specific vs unified interfaces
- Adapter configuration

#### Request Handlers
- Main interfaces for each capability
- Request/Response pattern
- Error handling

#### Decision Tree
- Automatic provider selection
- Criteria-based routing
- Fallback mechanisms

#### Middleware
- Request/response processing
- Common middleware (logging, caching, etc.)
- Custom middleware creation

### 2. Message Types (for Chat)
- UserMessage, AssistantMessage, SystemMessage
- Message content types (text, images)
- Tool messages and function calling

### 3. Configuration Patterns
- Symfony Bundle configuration
- Standalone setup
- Environment variables
- Provider credentials

### 4. Common Patterns
- Streaming responses
- Batch processing
- Error handling and retries
- Caching strategies

## Content Guidelines

- Define each concept clearly
- Show how concepts relate to each other
- Include simple code examples
- Link to detailed implementation guides
- Keep it conceptual, not implementation-heavy