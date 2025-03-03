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

namespace ModelflowAi\Ollama\Responses\Chat;

use ModelflowAi\ApiClient\Responses\MetaInformation;
use ModelflowAi\ApiClient\Responses\Usage;

final readonly class CreateResponse
{
    private function __construct(
        public string $model,
        public int $createdAt,
        public CreateResponseMessage $message,
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
     *     message: array{
     *         role: string,
     *         content: ?string,
     *     },
     *     done: bool,
     *     total_duration: int|null,
     *     load_duration: int|null,
     *     prompt_eval_count?: int|null,
     *     prompt_eval_duration: int|null,
     *     eval_count: int|null,
     *     eval_duration?: int|null,
     * } $attributes
     */
    public static function from(array $attributes, MetaInformation $meta): self
    {
        return new self(
            $attributes['model'],
            (new \DateTimeImmutable($attributes['created_at']))->getTimestamp(),
            CreateResponseMessage::from($attributes['message']),
            $attributes['done'],
            $attributes['total_duration'] ?? 0,
            $attributes['load_duration'] ?? 0,
            $attributes['prompt_eval_duration'] ?? 0,
            $attributes['eval_duration'] ?? 0,
            Usage::from([
                'prompt_tokens' => $attributes['prompt_eval_count'] ?? 0,
                'completion_tokens' => $attributes['eval_count'] ?? 0,
                'total_tokens' => ($attributes['prompt_eval_count'] ?? 0) + ($attributes['eval_count'] ?? 0),
            ]),
            $meta,
        );
    }
}
