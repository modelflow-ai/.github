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

final readonly class CreateStreamedResponse
{
    private function __construct(
        public string $model,
        public int $createdAt,
        public int $index,
        public CreateStreamedResponseMessage $message,
        public bool $done,
        public ?int $totalDuration,
        public ?int $loadDuration,
        public ?int $promptEvalDuration,
        public ?int $evalDuration,
        public ?Usage $usage,
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
     *     total_duration?: int,
     *     load_duration?: int,
     *     prompt_eval_count?: int|null,
     *     prompt_eval_duration?: int,
     *     eval_count?: int,
     *     eval_duration?: int,
     * } $attributes
     */
    public static function from(int $index, array $attributes, MetaInformation $meta): self
    {
        return new self(
            $attributes['model'],
            (new \DateTimeImmutable($attributes['created_at']))->getTimestamp(),
            $index,
            CreateStreamedResponseMessage::from($attributes['message']),
            $attributes['done'],
            $attributes['total_duration'] ?? null,
            $attributes['load_duration'] ?? null,
            $attributes['prompt_eval_duration'] ?? null,
            $attributes['eval_duration'] ?? null,
            $attributes['eval_count'] ?? null ? Usage::from([
                'prompt_tokens' => $attributes['prompt_eval_count'] ?? 0,
                'completion_tokens' => $attributes['eval_count'],
                'total_tokens' => ($attributes['prompt_eval_count'] ?? 0) + $attributes['eval_count'],
            ]) : null,
            $meta,
        );
    }
}
