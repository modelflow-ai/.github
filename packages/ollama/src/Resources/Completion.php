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

namespace ModelflowAi\Ollama\Resources;

use ModelflowAi\ApiClient\Resources\Concerns\Streamable;
use ModelflowAi\ApiClient\Transport\Payload;
use ModelflowAi\ApiClient\Transport\TransportInterface;
use ModelflowAi\Ollama\Responses\Completion\CreateResponse;
use Webmozart\Assert\Assert;

final readonly class Completion implements CompletionInterface
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
        $parameters['format'] ??= 'json';
        $parameters['stream'] = false;

        $payload = Payload::create('generate', $parameters);

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

        Assert::keyExists($parameters, 'prompt');
        Assert::string($parameters['prompt']);

        if (isset($parameters['format'])) {
            Assert::string($parameters['format']);
            Assert::inArray($parameters['format'], ['json']);
        }

        if (isset($parameters['template'])) {
            Assert::string($parameters['template']);
        }

        if (isset($parameters['context'])) {
            Assert::allFloat($parameters['context']);
        }
    }
}
