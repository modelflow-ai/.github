import { withMermaid } from "vitepress-plugin-mermaid";

export default withMermaid({
  title: 'Modelflow AI',
  description: 'Unified PHP AI Library - Connect to any AI provider with a single, elegant interface',
  base: '/',
  
  // Optionally, you can pass MermaidConfig
  mermaid: {
    // refer https://mermaid.js.org/config/setup/modules/mermaidAPI.html#mermaidapi-configuration-defaults for options
  },
  // Optionally, you can pass MermaidPluginConfig
  mermaidPlugin: {
    class: "mermaid" // set additional css classes for parent container 
  },

  themeConfig: {
    nav: [
      { text: 'Home', link: '/' },
      { text: 'Getting Started', link: '/getting-started/' },
      { 
        text: 'Capabilities',
        items: [
          { text: 'Chat', link: '/capabilities/chat/' },
          { text: 'Completion', link: '/capabilities/completion/' },
          { text: 'Embeddings', link: '/capabilities/embeddings/' },
          { text: 'Image', link: '/capabilities/image/' }
        ]
      },
      { text: 'Providers', link: '/providers/' },
      { text: 'Integration', link: '/integration/' },
      { text: 'Examples', link: '/examples/' }
    ],

    sidebar: {
      '/getting-started/': [
        {
          text: 'Getting Started',
          items: [
            { text: 'Overview', link: '/getting-started/' },
            { text: 'Quick Start', link: '/getting-started/quick-start' },
            { text: 'Installation', link: '/getting-started/installation' },
            { text: 'Architecture', link: '/getting-started/architecture' },
            { text: 'Core Concepts', link: '/getting-started/concepts' }
          ]
        }
      ],
      
      '/capabilities/chat/': [
        {
          text: 'Chat',
          items: [
            { text: 'Overview', link: '/capabilities/chat/' },
            { text: 'Basic Usage', link: '/capabilities/chat/basic-usage' },
            { text: 'Streaming', link: '/capabilities/chat/streaming' },
            { text: 'Function Calling', link: '/capabilities/chat/function-calling' },
            { text: 'Conversations', link: '/capabilities/chat/conversations' }
          ]
        }
      ],
      
      '/capabilities/completion/': [
        {
          text: 'Completion',
          items: [
            { text: 'Overview', link: '/capabilities/completion/' },
            { text: 'Basic Usage', link: '/capabilities/completion/basic-usage' },
            { text: 'Prompt Engineering', link: '/capabilities/completion/prompt-engineering' },
            { text: 'Use Cases', link: '/capabilities/completion/use-cases' }
          ]
        }
      ],
      
      '/capabilities/embeddings/': [
        {
          text: 'Embeddings',
          items: [
            { text: 'Overview', link: '/capabilities/embeddings/' },
            { text: 'Generating', link: '/capabilities/embeddings/generating' },
            { text: 'Storage', link: '/capabilities/embeddings/storage' },
            { text: 'Search', link: '/capabilities/embeddings/search' },
            { text: 'RAG', link: '/capabilities/embeddings/rag' }
          ]
        }
      ],
      
      '/capabilities/image/': [
        {
          text: 'Image',
          items: [
            { text: 'Overview', link: '/capabilities/image/' },
            { text: 'Generation', link: '/capabilities/image/generation' },
            { text: 'Analysis', link: '/capabilities/image/analysis' }
          ]
        }
      ],
      
      '/providers/': [
        {
          text: 'Providers',
          items: [
            { text: 'Overview', link: '/providers/' },
            { text: 'OpenAI', link: '/providers/openai' },
            { text: 'Anthropic', link: '/providers/anthropic' },
            { text: 'Ollama', link: '/providers/ollama' },
            { text: 'Google Gemini', link: '/providers/gemini' },
            { text: 'Mistral AI', link: '/providers/mistral' },
            { text: 'Fireworks.ai', link: '/providers/fireworksai' }
          ]
        }
      ],
      
      '/integration/': [
        {
          text: 'Integration',
          items: [
            { text: 'Overview', link: '/integration/' },
            { text: 'Symfony Bundle', link: '/integration/symfony' },
            { text: 'Standalone', link: '/integration/standalone' },
            { text: 'Extensions', link: '/integration/extensions' }
          ]
        },
        {
          text: 'Advanced',
          items: [
            { text: 'Middleware', link: '/integration/middleware' },
            { text: 'Performance', link: '/integration/performance' },
            { text: 'Deployment', link: '/integration/deployment' }
          ]
        }
      ],
      
      '/examples/': [
        {
          text: 'Examples',
          items: [
            { text: 'Overview', link: '/examples/' },
            { text: 'Chatbot', link: '/examples/chatbot' },
            { text: 'Document Q&A', link: '/examples/document-qa' },
            { text: 'Image Generation', link: '/examples/image-generation' },
            { text: 'Embeddings Search', link: '/examples/embeddings-search' }
          ]
        }
      ]
    },

    socialLinks: [
      { icon: 'github', link: 'https://github.com/modelflow-ai' }
    ],

    search: {
      provider: 'local'
    },

    footer: {
      message: 'Released under the MIT License.',
      copyright: 'Copyright © 2025 Modelflow AI'
    }
  }
});
