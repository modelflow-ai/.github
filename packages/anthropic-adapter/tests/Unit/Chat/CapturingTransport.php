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

namespace ModelflowAi\AnthropicAdapter\Tests\Unit\Chat;

use ModelflowAi\Anthropic\DataFixtures;
use ModelflowAi\ApiClient\Responses\MetaInformation;
use ModelflowAi\ApiClient\Transport\Payload;
use ModelflowAi\ApiClient\Transport\Response\ObjectResponse;
use ModelflowAi\ApiClient\Transport\Response\TextResponse;
use ModelflowAi\ApiClient\Transport\TransportInterface;

/**
 * Records the sent parameters so a test can assert that a field is absent, which the partial
 * payload matching of the mock transport cannot express.
 */
final class CapturingTransport implements TransportInterface
{
    /**
     * @var array<string, mixed>
     */
    public array $parameters = [];

    public function requestObject(Payload $payload): ObjectResponse
    {
        $this->parameters = $payload->parameters;

        return new ObjectResponse(DataFixtures::MESSAGES_CREATE_RESPONSE, MetaInformation::empty());
    }

    public function requestText(Payload $payload): TextResponse
    {
        throw new \BadMethodCallException();
    }

    public function requestStream(Payload $payload, ?callable $decoder = null): \Iterator
    {
        throw new \BadMethodCallException();
    }
}
