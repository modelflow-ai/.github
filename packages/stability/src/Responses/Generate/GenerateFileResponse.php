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

namespace ModelflowAi\Stability\Responses\Generate;

use ModelflowAi\ApiClient\Responses\MetaInformation;

final readonly class GenerateFileResponse
{
    /**
     * @param resource $resource
     */
    private function __construct(
        public string $id,
        public string $mimeType,
        public string $finishReason,
        public string $seed,
        public mixed $resource,
        public MetaInformation $meta,
    ) {
    }

    /**
     * @param resource $resource
     */
    public static function from(mixed $resource, MetaInformation $meta): self
    {
        return new self(
            $meta->headers['x-request-id'][0],
            $meta->headers['content-type'][0],
            $meta->headers['finish-reason'][0],
            $meta->headers['seed'][0],
            $resource,
            $meta,
        );
    }
}
