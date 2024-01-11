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

namespace ModelflowAi\Mistral\Transport;

use ModelflowAi\Mistral\Responses\MetaInformation;
use ModelflowAi\Mistral\Transport\Response\ObjectResponse;
use ModelflowAi\Mistral\Transport\Response\TextResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SymfonyHttpTransporter implements TransportInterface
{
    private readonly HttpClientInterface $client;

    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        HttpClientInterface $client,
        string $baseUrl,
        array $headers = [],
    ) {
        $this->client = $client->withOptions([
            'base_uri' => $baseUrl,
            'headers' => $headers,
        ]);
    }

    private function request(Payload $payload): ResponseInterface
    {
        return $this->client->request(
            $payload->method->value,
            $payload->resourceUri->__toString(),
            [
                'headers' => [
                    'Content-Type' => $payload->contentType->value,
                ],
                'json' => $payload->parameters,
            ],
        );
    }

    public function requestText(Payload $payload): TextResponse
    {
        $response = $this->request($payload);

        // @phpstan-ignore-next-line
        return new TextResponse($response->getContent(), MetaInformation::from($response->getHeaders()));
    }

    public function requestObject(Payload $payload): ObjectResponse
    {
        $response = $this->request($payload);

        // @phpstan-ignore-next-line
        return new ObjectResponse($response->toArray(), MetaInformation::from($response->getHeaders()));
    }
}
