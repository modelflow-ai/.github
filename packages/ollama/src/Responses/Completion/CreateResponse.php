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
use ModelflowAi\ApiClient\Responses\Usage;

final readonly class CreateResponse
{
    /**
     * @param float[] $context
     */
    private function __construct(
        public string $model,
        public int $createdAt,
        public string $response,
        public array $context,
        public bool $done,
        public int $totalDuration,
        public int $loadDuration,
        public int $promptEvalDuration,
        public int $evalDuration,
        public Usage $usage,
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
     *     total_duration: int,
     *     load_duration: int,
     *     prompt_eval_count?: int|null,
     *     prompt_eval_duration: int,
     *     eval_count: int,
     *     eval_duration: int,
     * } $attributes
     */
    public static function from(array $attributes, MetaInformation $meta): self
    {
        return new self(
            $attributes['model'],
            (new \DateTimeImmutable($attributes['created_at']))->getTimestamp(),
            $attributes['response'],
            $attributes['context'],
            $attributes['done'],
            $attributes['total_duration'],
            $attributes['load_duration'],
            $attributes['prompt_eval_duration'],
            $attributes['eval_duration'],
            Usage::from([
                'prompt_tokens' => $attributes['prompt_eval_count'] ?? 0,
                'completion_tokens' => $attributes['eval_count'],
                'total_tokens' => ($attributes['prompt_eval_count'] ?? 0) + $attributes['eval_count'],
            ]),
            $meta,
        );
    }
}
