# ModelflowAI<br/>Mistral

The Mistral package is a comprehensive API client for [Mistral AI](https://mistral.ai/), developed using PHP. It
provides a robust and efficient interface for interacting with the Mistral AI model, enabling developers to seamlessly
integrate AI capabilities into their PHP applications.

This package is designed with a focus on ease of use, performance, and flexibility. It allows developers to leverage the
full potential of the Mistral AI model, from creating chat conversations to getting chat completions, all through a
simple and intuitive API.

While the Mistral package can be used in conjunction with other packages, it is important to note that it operates
independently and is not directly connected to any other package or system. This independence ensures that developers
can integrate the Mistral package into their projects without any dependencies or conflicts.

## Installation

To install the Mistral package, you need to have PHP 8.2 or higher and Composer installed on your machine. Then, you can
add the package to your project by running the following command:

```bash
composer require modelflow-ai/mistral
```

## Examples

Here are some examples of how you can use the Mistral package in your PHP applications. You can find more detailed
examples in the [examples directory](examples).

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

### Using the Embeddings Resource

The Embeddings resource allows you to generate and manipulate embeddings for your data.

```php
$embeddings = $client->embeddings();

// Generate embeddings for your data
$parameters = [
    'model' => 'mistral-medium',
    'texts' => ['text1', 'text2']
];
$response = $embeddings->create($parameters);

// The response is an instance of CreateResponse
echo $response->id;
```

## API Documentation

For more detailed information about the Mistral API, please refer to
the [official API documentation](https://docs.mistral.ai/api).

## Open Points

### Model API

The Model API is another area that we are actively working on. Once completed, this will provide users with the ability
to manage and interact with their AI models directly from the Mistral package.

### Streaming Responses

We are currently working on implementing a feature to stream responses. This will allow users to receive data in
real-time, improving the efficiency and responsiveness of their applications.

## Testing

To run the tests, use PHPUnit:

```bash
composer test
```

## Contributing

Contributions are welcome. If you encounter any problems or have any suggestions, please open an issue directly in this
repository. You can also submit a pull request if you have made changes that you would like to share.

## License

This project is licensed under the MIT License. For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
