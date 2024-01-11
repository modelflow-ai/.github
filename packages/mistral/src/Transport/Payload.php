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

use ModelflowAi\Mistral\Transport\Enums\ContentType;
use ModelflowAi\Mistral\Transport\Enums\Method;
use ModelflowAi\Mistral\Transport\ValueObjects\ResourceUri;

readonly class Payload
{
    /**
     * @param array<string, mixed> $parameters
     */
    private function __construct(
        public ContentType $contentType,
        public Method $method,
        public ResourceUri $resourceUri,
        public array $parameters = [],
    ) {
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public static function create(string $resource, array $parameters): self
    {
        return new self(
            contentType: ContentType::JSON,
            method: Method::POST,
            resourceUri: ResourceUri::get($resource),
            parameters: $parameters,
        );
    }
}
