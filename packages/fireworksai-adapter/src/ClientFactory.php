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

namespace ModelflowAi\FireworksAiAdapter;

use Http\Discovery\Psr18ClientDiscovery;
use ModelflowAi\FireworksAiAdapter\Http\Psr18ClientDecorator;
use OpenAI\Contracts\ClientContract;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class ClientFactory
{
    public static function create(): self
    {
        return new self();
    }

    private string $apiKey;

    public function withApiKey(string $apiKey): self
    {
        $this->apiKey = \trim($apiKey);

        return $this;
    }

    public function make(): ClientContract
    {
        $client = new Psr18ClientDecorator(Psr18ClientDiscovery::find());

        return \OpenAI::factory()
            ->withApiKey($this->apiKey)
            ->withBaseUri('https://api.fireworks.ai/inference/v1')
            ->withHttpClient($client)
            ->withStreamHandler(static fn (RequestInterface $request): ResponseInterface => $client->sendRequest($request))
            ->make();
    }
}
