# ModelflowAI<br/>Mistral Adapter

The adapter integrates Mistral AI models into Modelflow  AI.

## Installation

To install the Mistral Adapter package, you need to have PHP 8.2 or higher and Composer installed on your machine. Then,
you can add the package to your project by running the following command:

```bash
composer require modelflow-ai/mistral-adapter
```

## Examples

Here are some examples of how you can use the Mistral package in your PHP applications. You can find more detailed
examples in the [examples directory](examples).

## Usage

First, initialize the client:

```php
use ModelflowAi\Mistral\Mistral;

$client = Mistral::client('your-api-key');
```

Then, you can use the `ModelAdapter`:

```php
use ModelflowAi\Mistral\Model;
use ModelflowAi\MistralAdapter\Model\MistralChatModelAdapter;

$modelAdapter = new MistralChatModelAdapter($client);
$response = $modelAdapter->create([
    'model' => Model::TINY->value,
    'messages' => [
        [
            'role' => 'system',
            'content' => 'your-content',
        ],
    ],
]);
```

And the `EmbeddingsAdapter`:

```php
use ModelflowAi\MistralAdapter\Embeddings\MistralEmbeddingAdapter;

$embeddingsAdapter = new MistralEmbeddingAdapter($client);
$response = $embeddingsAdapter->create([
    'input' => ['your-input'],
]);
```

Remember to replace `'your-content'`, and `'your-input'` with the actual values you want to use.

## Contributing

Contributions are welcome. Please open an issue or submit a pull request in the main repository
at [https://github.com/modelflow-ai/modelflow-ai](https://github.com/modelflow-ai/modelflow-ai).

## License

This project is licensed under the MIT License. For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
