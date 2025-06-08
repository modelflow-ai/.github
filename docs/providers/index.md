# Providers Overview

> **🚧 Coming Soon** - This documentation is currently under development. The content below provides a preview based on actual provider capabilities.

## Provider Comparison

Complete table showing which providers support which capabilities:

| Provider | Chat | Completion | Embeddings | Images | Streaming | Local |
|----------|------|------------|------------|--------|-----------|-------|
| **OpenAI** | ✅ | ❌ | ✅ | ✅ | ✅ | ❌ |
| **Anthropic** | ✅ | ❌ | ❌ | ✅ (Vision) | ✅ | ❌ |
| **Ollama** | ✅ | ✅ | ✅ | ✅ (Vision) | ✅ | ✅ |
| **Google Gemini** | ✅ | ❌ | ❌ | ✅ (Vision) | ✅ | ❌ |
| **Mistral AI** | ✅ | ❌ | ✅ | ❌ | ✅ | ❌ |
| **Fireworks.ai** | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |

### 2. Provider Selection Guide
- **For Privacy** → Ollama (local models)
- **For Quality** → OpenAI GPT-4, Anthropic Claude
- **For Speed** → Fireworks.ai
- **For Cost** → Mistral AI, Fireworks.ai
- **For Vision** → OpenAI GPT-4V, Gemini Pro Vision

### 3. Quick Setup Examples
Code examples for each provider showing basic setup.

### 4. Provider-Specific Features
- Unique capabilities of each provider
- Model recommendations
- Pricing considerations
- Rate limits and quotas

## Individual Provider Pages

Each provider should have its own detailed page:

- **openai.md** - Complete OpenAI documentation
- **anthropic.md** - Anthropic Claude documentation  
- **ollama.md** - Local model setup and usage
- **gemini.md** - Google Gemini integration
- **mistral.md** - Mistral AI configuration
- **fireworksai.md** - Fireworks.ai setup

## Content Guidelines

- Focus on practical setup and usage
- Include working code examples
- Document provider-specific features
- Explain cost and performance trade-offs
- Troubleshooting for each provider