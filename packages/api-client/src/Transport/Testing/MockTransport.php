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

namespace ModelflowAi\ApiClient\Transport\Testing;

use ModelflowAi\ApiClient\Responses\MetaInformation;
use ModelflowAi\ApiClient\Transport\Payload;
use ModelflowAi\ApiClient\Transport\Response\ObjectResponse;
use ModelflowAi\ApiClient\Transport\Response\TextResponse;
use ModelflowAi\ApiClient\Transport\TransportInterface;
use Symfony\Component\HttpClient\Chunk\DataChunk;
use Symfony\Contracts\HttpClient\ChunkInterface;

class MockTransport implements TransportInterface
{
    public function __construct(
        private readonly MockResponseMatcher $matcher,
    ) {
    }

    public function requestText(Payload $payload): TextResponse
    {
        $response = $this->matcher->matchTextResponse($payload);
        if (!$response instanceof TextResponse) {
            throw new \RuntimeException('No matching response found for payload');
        }

        return $response;
    }

    public function requestObject(Payload $payload): ObjectResponse
    {
        $response = $this->matcher->matchObjectResponse($payload);
        if (!$response instanceof ObjectResponse) {
            throw new \RuntimeException('No matching response found for payload');
        }

        return $response;
    }

    public function requestStream(Payload $payload, ?callable $decoder = null): \Iterator
    {
        $response = $this->matcher->matchStreamedResponse($payload);
        if (!$response instanceof StreamedResponse) {
            throw new \RuntimeException('No matching response found for payload');
        }

        if (!$decoder) {
            $decoder = static fn (ChunkInterface $chunk) => [\json_decode($chunk->getContent(), true)];
        }

        foreach ($response->chunks as $chunk) {
            foreach ($decoder(new DataChunk(0, $chunk)) as $data) {
                yield new ObjectResponse($data, MetaInformation::empty());
            }
        }
    }
}
