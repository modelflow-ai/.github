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

final readonly class GenerateBase64Response
{
    private function __construct(
        public string $id,
        public string $mimeType,
        public string $finishReason,
        public string $seed,
        public string $base64,
        public MetaInformation $meta,
    ) {
    }

    /**
     * @param array{
     *     finish_reason: string,
     *     seed: string,
     *     image: string,
     * } $attributes
     */
    public static function from(array $attributes, MetaInformation $meta): self
    {
        $contentType = $meta->headers['content-type'];
        $mimeType = \preg_replace('/^application\/json; type=(.*)$/', '$1', $contentType) ?? 'image/png';

        return new self(
            $meta->headers['x-request-id'],
            $mimeType,
            $attributes['finish_reason'],
            $attributes['seed'],
            $attributes['image'],
            $meta,
        );
    }
}
