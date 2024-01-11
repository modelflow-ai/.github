# Mistral

The Mistral package integrates the Mistral AI model into Modelflow AI, providing a seamless way to leverage the power of
AI in your PHP applications.

## Installation

Ensure you have PHP 8.2 or higher and Composer installed on your machine. Then, add the package to your project by
running the following command:

```bash
composer require modelflow-ai/mistral
```

## Usage

### Creating a Client

First, you need to create a client. The client is the main entry point to interact with the Mistral AI model. You can
create a client using the `Mistral` class:

```php
use ModelflowAi\Mistral\Mistral;

$client = Mistral::client('your-api-key');
```

### Using the Chat Resource

The Chat resource allows you to create chat conversations and get chat completions.

```php
$chat = $client->chat();

// Create a chat conversation
$parameters = [
    'model' => 'mistral-medium',
    'messages' => [
        [
            'role' => 'system',
            'content' => 'You are a helpful assistant.'
        ],
        [
            'role' => 'user',
            'content' => 'Who won the world series in 2020?'
        ]
    ]
];
$response = $chat->create($parameters);

// The response is an instance of CreateResponse
echo $response->id;
```

## API Documentation

For more detailed information about the Mistral API, please refer to
the [official API documentation](https://docs.mistral.ai/api).

## Open Points

### Embeddings

The integration of embeddings into the Mistral package is currently under development. This feature will allow users to
generate and manipulate embeddings for their data, providing a powerful tool for machine learning tasks.

### Model API

The Model API is another area that we are actively working on. Once completed, this will provide users with the ability
to manage and interact with their AI models directly from the Mistral package.

## Contributing

Contributions are welcome. If you encounter any problems or have any suggestions, please open an issue directly in this
repository. You can also submit a pull request if you have made changes that you would like to share.

## License

This project is licensed under the MIT License. For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
