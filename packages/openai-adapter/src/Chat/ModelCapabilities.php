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
     * Reasoning effort levels per model family. The GPT-5.6 family dropped `minimal`, the lowest
     * level of the generations before it, and gained `none`.
     *
     * @var array<string, list<string>>
     */
    private const REASONING_EFFORTS = [
        'gpt-5.6' => ['none', 'low', 'medium', 'high', 'xhigh', 'max'],
    ];

    /**
     * Families whose Chat Completions endpoint refuses function tools while reasoning is active.
     * The default effort of `medium` is enough to trigger it, so sending nothing does not help; the
     * API names `reasoning_effort: none` as the way out.
     *
     * @var list<string>
     */
    private const TOOLS_REQUIRE_NO_REASONING = [
        'gpt-5.6',
    ];

    public static function supportsSampling(string $model): bool
    {
        return !self::matches($model, self::SAMPLING_REMOVED);
    }

    public static function supportsReasoningEffortNone(string $model): bool
    {
        $efforts = self::reasoningEffortsFor($model);

        return null !== $efforts && \in_array(ReasoningEffortEnum::NONE->value, $efforts, true);
    }

    public static function toolsRequireNoReasoning(string $model): bool
    {
        return self::matches($model, self::TOOLS_REQUIRE_NO_REASONING);
    }

    public static function supportsReasoningEffort(string $model, ReasoningEffortEnum $effort): bool
    {
        $efforts = self::reasoningEffortsFor($model);

        if (null === $efforts) {
            // Nothing is documented here for this model, so only `none` is refused: it exists on
            // the GPT-5.6 family and not on the reasoning models before it. Any other level is the
            // caller's choice.
            return ReasoningEffortEnum::NONE !== $effort;
        }

        return \in_array($effort->value, $efforts, true);
    }

    /**
     * @return list<string>|null
     */
    private static function reasoningEffortsFor(string $model): ?array
    {
        foreach (self::REASONING_EFFORTS as $candidate => $efforts) {
            if (self::matches($model, [$candidate])) {
                return $efforts;
            }
        }

        return null;
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
