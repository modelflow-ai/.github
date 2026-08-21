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

namespace ModelflowAi\OpenaiAdapter\Chat;

/**
 * Request surface differences between the reasoning models and the rest of the OpenAI catalog.
 *
 * @see https://developers.openai.com/api/docs/guides/reasoning
 */
final class ModelCapabilities
{
    /**
     * Reasoning models accept temperature only at its default of 1 and answer anything else with an
     * unsupported_value error.
     *
     * @var list<string>
     */
    private const SAMPLING_REMOVED = [
        'gpt-5',
        'o1',
        'o3',
        'o4',
    ];

    /**
     * Models documented to accept `reasoning_effort: none`. Older reasoning models know `minimal` as
     * their lowest level instead, so they are deliberately not listed.
     *
     * @var list<string>
     */
    private const REASONING_EFFORT_NONE = [
        'gpt-5.6',
    ];

    public static function supportsSampling(string $model): bool
    {
        return !self::matches($model, self::SAMPLING_REMOVED);
    }

    public static function supportsReasoningEffortNone(string $model): bool
    {
        return self::matches($model, self::REASONING_EFFORT_NONE);
    }

    /**
     * @param list<string> $models
     */
    private static function matches(string $model, array $models): bool
    {
        foreach ($models as $candidate) {
            // Covers both the bare model and its variants, such as "gpt-5.6-sol".
            if ($model === $candidate || \str_starts_with($model, $candidate . '-') || \str_starts_with($model, $candidate . '.')) {
                return true;
            }
        }

        return false;
    }
}
