# Modelflow AI

Modelflow AI is a comprehensive AI package that integrates various AI models and embeddings into a unified interface. It
is written in PHP and uses Composer for dependency management.

## Packages

This repository consists of several packages, each residing in its own directory under the `packages/` directory:

- **Core**: The core functionalities of Modelflow AI. [More Info](https://github.com/modelflow-ai/core)
- **Elasticsearch Embeddings Store**: This package stores embeddings in Elasticsearch. [More Info](https://github.com/modelflow-ai/elasticsearch-embeddings-store)
- **Embeddings**: Handles the embeddings for the AI models. [More Info](https://github.com/modelflow-ai/embeddings)
- **Experts**: It provides a set of tools for so called experts comparable to OpenAI GPTs or OpenGPTs. [More Info](https://github.com/modelflow-ai/experts)
- **Mistral Adapter**: The adapter for the Mistral API client. [More Info](https://github.com/modelflow-ai/mistral-adapter)
- **Ollama Adapter**: The adapter for the Ollama API client. [More Info](https://github.com/modelflow-ai/ollama-adapter)
- **OpenAI Adapter**: The adapter for integrating OpenAI models. [More Info](https://github.com/modelflow-ai/openai-adapter)
- **Prompt Template**: Provides templates for AI prompts. [More Info](https://github.com/modelflow-ai/prompt-template)
- **Qdrant Embeddings Store**: This package stores embeddings in Qdrant. [More Info](https://github.com/modelflow-ai/qdrant-embeddings-store)
- **Tools**: Contains tools to extend models in a Modelflow AI Request. [More Info](https://github.com/modelflow-ai/tools)

Independent packages:

- **Api-Client**: A basic API client. [More Info](https://github.com/modelflow-ai/api-client)
- **Mistral**: A comprehensive API client for Mistral AI. [More Info](https://github.com/modelflow-ai/mistral)
- **Ollama**: A comprehensive API client for Ollama. [More Info](https://github.com/modelflow-ai/ollama)

## Integrations

Modelflow AI integrates with the following php frameworks:

- **Symfony** [More Info](https://github.com/modelflow-ai/symfony-bundle)

## Installation

To install Modelflow AI, you need to have PHP 8.2 or higher and Composer installed on your machine. Then, you can clone
this repository and run `composer install` in the root directory.

## Usage

Each package has its own usage instructions. Please refer to the README file in each package's directory for specific
usage instructions.

## Contributing

Contributions are welcome. Please open an issue to discuss your idea or submit a pull request.

## License

This project is licensed under the MIT License. For the full copyright and license information, please view the
[LICENSE](LICENSE) file that was distributed with this source code.
