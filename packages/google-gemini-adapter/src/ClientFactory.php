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

namespace ModelflowAi\GoogleGeminiAdapter;

use Gemini\Contracts\ClientContract;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ClientFactory
{
    public static function create(): self
    {
        return new self();
    }

    private string $apiKey;

    public function withApiKey(string $apiKey): self
    {
        if ('' === $apiKey || '0' === $apiKey) {
            throw new \InvalidArgumentException('A valid Google Gemini API key must be provided.');
        }
        $this->apiKey = $apiKey;

        return $this;
    }

    public function make(): ClientContract
    {
        $client = Psr18ClientDiscovery::find();

        return \Gemini::factory()
            ->withApiKey($this->apiKey)
            ->withHttpClient($client)
            ->withStreamHandler(static fn (RequestInterface $request): ResponseInterface => $client->sendRequest($request))
            ->make();
    }
}
