<?php

namespace ModelflowAi\Mistral;


use ModelflowAi\Mistral\Transport\SymfonyHttpTransporter;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class Factory
{
    private HttpClientInterface $client;

    private string $baseUrl = 'https://api.mistral.ai/v1/';

    private string $apiKey;

    public function withHttpClient(HttpClientInterface $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function withBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    public function withApiKey(string $apiKey): self
    {
        $this->apiKey = trim($apiKey);

        return $this;
    }

    public function make(): Client
    {
        $transporter = new SymfonyHttpTransporter($this->client ?? HttpClient::create(), $this->baseUrl, [
            'Authorization' => 'Bearer ' . $this->apiKey,
        ]);

        return new Client($transporter);
    }
}
