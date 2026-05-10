<br/>
<div align="center">
 <img alt="Modelflow AI Logo" src="https://avatars.githubusercontent.com/u/152068817?s=768&amp;v=4" width="200" height="200">
</div>

<h1 align="center">
Modelflow AI<br/><br/>
</h1>

<br/>

<div align="center">
<strong>Monorepository</strong> for **Modelflow AI**
<br/><br/>
Modelflow AI is a set of comprehensive AI packages that integrates various AI models and embeddings into a unified
interface. It is written in PHP and uses Composer for dependency management.
<br/><br/>

![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/modelflow-ai/.github/quality-assurance.yml) ![GitHub commit activity](https://img.shields.io/github/commit-activity/t/modelflow-ai/.github) ![License](https://img.shields.io/github/license/modelflow-ai/modelflow-ai)

</div>

<br/>

> **Note**:
> This project is heavily under development and any feedback is greatly appreciated.

<br/>

## Packages

This repository consists of several packages, each residing in its own directory under the `packages/` directory:

- **Anthropic Adapter**: The adapter for the Anthropic API client. [More Info](https://github.com/modelflow-ai/anthropic-adapter)
- **Chat**: Chat functionalities for Modelflow AI. [More Info](https://github.com/modelflow-ai/chat)
- **Completion**: Completion functionalities for Modelflow AI. [More Info](https://github.com/modelflow-ai/completion)
- **Decision Tree**: A decision tree represents a series of decisions and the possible outcome of those decisions. [More Info](https://github.com/modelflow-ai/decision-tree)
- **Elasticsearch Embeddings Store**: This package stores embeddings in Elasticsearch. [More Info](https://github.com/modelflow-ai/elasticsearch-embeddings-store)
- **Embeddings**: Handles the embeddings for the AI models. [More Info](https://github.com/modelflow-ai/embeddings)
- **Experts**: It provides a set of tools for so called experts comparable to OpenAI GPTs or OpenGPTs. [More Info](https://github.com/modelflow-ai/experts)
- **Fireworks.ai Adapter**: The adapter for integrating LLMs by fireworks.ai. [More Info](https://github.com/modelflow-ai/fireworksai-adapter)
- **Google Gemini Adapter**: The adapter for the Google Gemini API. [More Info](https://github.com/modelflow-ai/google-gemini-adapter)
- **Image**: Handles image requests like generation or edits. [More Info](https://github.com/modelflow-ai/image)
- **Mistral Adapter**: The adapter for the Mistral API client. [More Info](https://github.com/modelflow-ai/mistral-adapter)
- **Ollama Adapter**: The adapter for the Ollama API client. [More Info](https://github.com/modelflow-ai/ollama-adapter)
- **OpenAI Adapter**: The adapter for integrating OpenAI models. [More Info](https://github.com/modelflow-ai/openai-adapter)
- **Prompt Template**: Provides templates for AI prompts. [More Info](https://github.com/modelflow-ai/prompt-template)
- **Qdrant Embeddings Store**: This package stores embeddings in Qdrant. [More Info](https://github.com/modelflow-ai/qdrant-embeddings-store)
- **Tools**: Contains tools to extend models in a Modelflow AI Request. [More Info](https://github.com/modelflow-ai/tools)

Independent packages:

- **Anthropic**: A comprehensive API client for Anthropic AI. [More Info](https://github.com/modelflow-ai/anthropic)
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

