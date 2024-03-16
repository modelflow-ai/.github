# ModelflowAI<br/>Ollama

Ollama is a PHP package that provides an easy-to-use client for the ollama API.

## Installation

To install the Ollama package, you need to have PHP 8.2 or higher and Composer installed on your machine. Then, you can
add the package to your project by running the following command:

```bash
composer require modelflow-ai/ollama
```

## Examples

Here are some examples of how you can use the Mistral package in your PHP applications. You can find more detailed
examples in the [examples directory](examples).

## Usage

```php
use ModelflowAi\Ollama\Ollama;

// Create a client instance
$client = Ollama::client();

// Use the client
$chat = $client->chat();
$completion = $client->completion();
$embeddings = $client->embeddings();

// Example usage of chat
$chatResponse = $chat->create([
    'model' => 'llama2',
    'messages' => [['role' => 'user', 'content' => 'Hello, world!']],
]);
echo $chatResponse->message->content;

// Example usage of completion
$completionResponse = $completion->create([
    'model' => 'llama2',
    'prompt' => 'Once upon a time',
]);
echo $completionResponse->response;

// Example usage of embeddings
$embeddingsResponse = $embeddings->create(['prompt' => 'Hello, world!']);
echo $embeddingsResponse->embedding;
```

For more examples, see the [examples](examples) directory.

## Testing & Code Quality

To run the tests and all the code quality tools with the following commands:

```bash
composer fix
composer lint
composer test
```

## Open Points

### Model API

The Model API is another area that we are actively working on. Once completed, this will provide users with the ability
to manage and interact with their AI models directly from the Ollama package.

## Contributing

Contributions are welcome. Please open an issue or submit a pull request in the main repository
at [https://github.com/modelflow-ai/modelflow-ai](https://github.com/modelflow-ai/modelflow-ai).

## License

This project is licensed under the MIT License. For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
