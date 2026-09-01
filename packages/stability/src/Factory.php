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

namespace ModelflowAi\Stability;

use ModelflowAi\ApiClient\Transport\SymfonyHttpTransporter;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class Factory
{
    private HttpClientInterface $httpClient;

    private string $baseUrl = 'https://api.stability.ai/v2beta/stable-image/';

    private ?string $apiKey = null;

    public function withHttpClient(HttpClientInterface $client): self
    {
        $this->httpClient = $client;

        return $this;
    }

    public function withBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    public function withApiKey(string $apiKey): self
    {
        $this->apiKey = \trim($apiKey);

        return $this;
    }

    public function make(): ClientInterface
    {
        if (!$this->apiKey) {
            throw new \RuntimeException('API key is required to create a client');
        }

        $transporter = new SymfonyHttpTransporter($this->httpClient ?? HttpClient::create(), $this->baseUrl, \array_filter([
            'authorization' => \sprintf('Bearer %s', $this->apiKey),
            'accept' => 'image/*',
        ]));

        return new Client($transporter);
    }
}
