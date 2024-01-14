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

use ModelflowAi\ApiClient\Transport\Payload;
use ModelflowAi\ApiClient\Transport\TransportInterface;
use ModelflowAi\Mistral\Model;
use ModelflowAi\Mistral\Responses\Embeddings\CreateResponse;
use Webmozart\Assert\Assert;

final readonly class Embeddings implements EmbeddingsInterface
{
    public function __construct(
        private TransportInterface $transport,
    ) {
    }

    public function create(array $parameters): CreateResponse
    {
        $this->validateParameters($parameters);
        $parameters['encoding_format'] = 'float';
        $parameters['model'] ??= Model::EMBED->value;

        $payload = Payload::create('embeddings', $parameters);

        $response = $this->transport->requestObject($payload);

        // @phpstan-ignore-next-line
        return CreateResponse::from($response->data, $response->meta);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function validateParameters(array $parameters): void
    {
        Assert::keyNotExists($parameters, 'encoding_format');

        if (isset($parameters['model'])) {
            Assert::string($parameters['model']);
        }

        Assert::keyExists($parameters, 'input');
        Assert::isArray($parameters['input']);
        foreach ($parameters['input'] as $input) {
            Assert::string($input);
        }

        if (isset($parameters['encoding_format'])) {
            Assert::string($parameters['encoding_format']);
            Assert::inArray($parameters['encoding_format'], ['float']);
        }
    }
}
