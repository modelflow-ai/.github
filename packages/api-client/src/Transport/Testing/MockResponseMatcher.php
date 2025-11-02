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

use ModelflowAi\ApiClient\Transport\Payload;
use ModelflowAi\ApiClient\Transport\Response\ObjectResponse;
use ModelflowAi\ApiClient\Transport\Response\RawResponse;
use ModelflowAi\ApiClient\Transport\Response\Response;
use ModelflowAi\ApiClient\Transport\Response\TextResponse;

class MockResponseMatcher
{
    /**
     * @var array<array{0: PartialPayload, 1: Response}>
     */
    private array $mockResponses = [];

    public function addResponse(PartialPayload $partialPayload, Response $response): void
    {
        $this->mockResponses[] = [$partialPayload, $response];
    }

    private function matchResponse(Payload $payload): ?Response
    {
        foreach ($this->mockResponses as [$partialPayload, $response]) {
            if ($this->payloadMatches($payload, $partialPayload)) {
                return $response;
            }
        }

        return null;
    }

    public function matchTextResponse(Payload $payload): ?TextResponse
    {
        $response = $this->matchResponse($payload);
        if ($response instanceof TextResponse) {
            return $response;
        }

        return null;
    }

    public function matchObjectResponse(Payload $payload): ?ObjectResponse
    {
        $response = $this->matchResponse($payload);
        if ($response instanceof ObjectResponse) {
            return $response;
        }

        return null;
    }

    public function matchStreamedResponse(Payload $payload): ?StreamedResponse
    {
        $response = $this->matchResponse($payload);
        if ($response instanceof StreamedResponse) {
            return $response;
        }

        return null;
    }

    public function matchRawResponse(Payload $payload): ?RawResponse
    {
        $response = $this->matchResponse($payload);
        if ($response instanceof RawResponse) {
            return $response;
        }

        return null;
    }

    private function payloadMatches(Payload $payload, PartialPayload $partialPayload): bool
    {
        if ($payload->contentType->name !== $partialPayload->contentType->name
            || $payload->method->name !== $partialPayload->method->name
            || false === $payload->resourceUri->equals($partialPayload->resourceUri)
        ) {
            return false;
        }

        foreach ($partialPayload->partialParameters ?? [] as $key => $value) {
            if (!\array_key_exists($key, $payload->parameters)
                || $payload->parameters[$key] !== $value
            ) {
                return false;
            }
        }

        return true;
    }
}
