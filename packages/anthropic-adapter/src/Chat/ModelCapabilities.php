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

namespace ModelflowAi\AnthropicAdapter\Chat;

/**
 * Request-surface differences between Claude generations. Anthropic answers with a 400 instead of
 * ignoring an unsupported field, so the adapter has to know which model it is talking to.
 *
 * @see https://platform.claude.com/docs/en/docs/build-with-claude/extended-thinking
 */
final class ModelCapabilities
{
    /**
     * Sampling (temperature, top_p, top_k) was removed with Opus 4.7 and is rejected since.
     *
     * @var list<string>
     */
    private const SAMPLING_REMOVED = [
        'claude-fable-5',
        'claude-mythos-5',
        'claude-mythos-preview',
        'claude-opus-5',
        'claude-sonnet-5',
        'claude-opus-4-8',
        'claude-opus-4-7',
    ];

    /**
     * Models accepting the `thinking` parameter. Older models expect `budget_tokens`, which this
     * adapter does not send.
     *
     * @var list<string>
     */
    private const THINKING_SUPPORTED = [
        'claude-fable-5',
        'claude-mythos-5',
        'claude-mythos-preview',
        'claude-opus-5',
        'claude-sonnet-5',
        'claude-opus-4-8',
        'claude-opus-4-7',
        'claude-opus-4-6',
        'claude-sonnet-4-6',
    ];

    /**
     * Models thinking adaptively when `thinking` is omitted. Everywhere else omitting it means no
     * thinking, so `disabled` only has to be sent for these.
     *
     * @var list<string>
     */
    private const THINKING_ON_BY_DEFAULT = [
        'claude-fable-5',
        'claude-mythos-5',
        'claude-mythos-preview',
        'claude-opus-5',
        'claude-sonnet-5',
    ];

    /**
     * Fable and Mythos always think; `disabled` is rejected with a 400.
     *
     * @var list<string>
     */
    private const THINKING_ALWAYS_ON = [
        'claude-fable-5',
        'claude-mythos-5',
        'claude-mythos-preview',
    ];

    public static function supportsSampling(string $model): bool
    {
        return !self::matches($model, self::SAMPLING_REMOVED);
    }

    public static function supportsThinking(string $model): bool
    {
        return self::matches($model, self::THINKING_SUPPORTED);
    }

    public static function thinksByDefault(string $model): bool
    {
        return self::matches($model, self::THINKING_ON_BY_DEFAULT);
    }

    public static function supportsDisabledThinking(string $model): bool
    {
        return self::supportsThinking($model) && !self::matches($model, self::THINKING_ALWAYS_ON);
    }

    /**
     * @param list<string> $models
     */
    private static function matches(string $model, array $models): bool
    {
        foreach ($models as $candidate) {
            // Dated snapshots such as "claude-haiku-4-5-20251001" share the base model's surface.
            if ($model === $candidate || \str_starts_with($model, $candidate . '-')) {
                return true;
            }
        }

        return false;
    }
}
