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

namespace ModelflowAi\Ollama\Responses\Completion;

use ModelflowAi\ApiClient\Responses\MetaInformation;

final readonly class CreateStreamedResponse
{
    private function __construct(
        public string $model,
        public int $createdAt,
        public int $index,
        public string $delta,
        public bool $done,
        public MetaInformation $meta,
    ) {
    }

    /**
     * @param array{
     *     model: string,
     *     created_at: string,
     *     response: string,
     *     context: float[],
     *     done: bool,
     * } $attributes
     */
    public static function from(int $index, array $attributes, MetaInformation $meta): self
    {
        return new self(
            $attributes['model'],
            (new \DateTimeImmutable($attributes['created_at']))->getTimestamp(),
            $index,
            $attributes['response'],
            $attributes['done'],
            $meta,
        );
    }
}
