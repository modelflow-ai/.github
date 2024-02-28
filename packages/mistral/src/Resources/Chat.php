<?php

declare(strict_types=1);

/*
 * This file is part of the Modelflow AI package.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ModelflowAi\Mistral\Resources;

use ModelflowAi\ApiClient\Resources\Concerns\Streamable;
use ModelflowAi\ApiClient\Transport\Payload;
use ModelflowAi\ApiClient\Transport\TransportInterface;
use ModelflowAi\Mistral\Model;
use ModelflowAi\Mistral\Responses\Chat\CreateResponse;
use Webmozart\Assert\Assert;

final readonly class Chat implements ChatInterface
{
    use Streamable;

    public function __construct(
        private TransportInterface $transport,
    ) {
    }

    public function create(array $parameters): CreateResponse
    {
        $this->ensureNotStreamed($parameters);
        $this->validateParameters($parameters);

        $payload = Payload::create('chat/completions', $parameters);

        $response = $this->transport->requestObject($payload);

        // @phpstan-ignore-next-line
        return CreateResponse::from($response->data, $response->meta);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function validateParameters(array $parameters): void
    {
        Assert::keyExists($parameters, 'model');
        Assert::string($parameters['model']);

        Assert::keyExists($parameters, 'messages');
        Assert::isArray($parameters['messages']);
        foreach ($parameters['messages'] as $message) {
            Assert::keyExists($message, 'role');
            Assert::string($message['role']);
            Assert::inArray($message['role'], ['system', 'user', 'assistant']);
            Assert::keyExists($message, 'content');
            Assert::string($message['content']);
        }

        if (isset($parameters['temperature'])) {
            Assert::float($parameters['temperature']);
            Assert::range($parameters['temperature'], 0.0, 1.0);
        }
        if (isset($parameters['top_p'])) {
            Assert::float($parameters['top_p']);
            Assert::range($parameters['top_p'], 0.0, 1.0);
        }
        if (isset($parameters['max_tokens'])) {
            Assert::integer($parameters['max_tokens']);
            Assert::greaterThan($parameters['max_tokens'], 0.0);
        }
        if (isset($parameters['safe_mode'])) {
            Assert::boolean($parameters['safe_mode']);
        }
        if (isset($parameters['random_seed'])) {
            Assert::integer($parameters['random_seed']);
        }

        if (!Model::from($parameters['model'])->jsonSupported()) {
            Assert::keyNotExists($parameters, 'response_format');
        } elseif (Model::from($parameters['model'])->jsonSupported() && isset($parameters['response_format'])) {
            Assert::keyExists($parameters, 'response_format');
            Assert::isArray($parameters['response_format']);
            Assert::keyExists($parameters['response_format'], 'type');
            Assert::inArray($parameters['response_format']['type'], ['json_object']);
        }
    }
}
