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
        'claude-haiku-5-5',
        'claude-opus-4-8',
        'claude-opus-4-7',
    ];

    /**
     * Models rejecting an assistant message at the end of the conversation, which is how a JSON
     * answer is started. Every later model of a family is covered by its base name.
     *
     * @var list<string>
     */
    private const PREFILL_REMOVED = [
        'claude-fable-5',
        'claude-mythos-5',
        'claude-mythos-preview',
        'claude-opus-5',
        'claude-sonnet-5',
        'claude-haiku-5-5',
        'claude-opus-4-8',
        'claude-opus-4-7',
        'claude-opus-4-6',
        'claude-sonnet-4-6',
    ];

    /**
     * Models accepting the `thinking` parameter. Older models expect `budget_tokens`, which this
     * adapter does not send.
     *
     * @var list<string>
     */
    private const THINKING_SUPPORTED = [
        'claude-haiku-5-5',
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
        'claude-haiku-5-5',
        'claude-fable-5',
        'claude-mythos-5',
        'claude-mythos-preview',
        'claude-opus-5',
        'claude-sonnet-5',
    ];

    /**
     * Fable, Mythos and Opus 5.5 always think; `disabled` is rejected with a 400.
     *
     * @var list<string>
     */
    private const THINKING_ALWAYS_ON = [
        'claude-fable-5',
        'claude-mythos-5',
        'claude-mythos-preview',
        'claude-opus-5-5',
    ];

    /**
     * Sonnet 5.5 rejects `disabled` and takes `between_tools` instead, which skips the up-front thinking.
     *
     * @var list<string>
     */
    private const THINKING_BETWEEN_TOOLS = [
        'claude-sonnet-5-5',
    ];

    public static function supportsSampling(string $model): bool
    {
        return !self::matches($model, self::SAMPLING_REMOVED);
    }

    public static function supportsPrefill(string $model): bool
    {
        return !self::matches($model, self::PREFILL_REMOVED);
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
        return self::supportsThinking($model)
            && !self::matches($model, self::THINKING_ALWAYS_ON)
            && !self::matches($model, self::THINKING_BETWEEN_TOOLS);
    }

    /**
     * The mode switching off the up-front thinking, null when the model cannot turn it off.
     */
    public static function disabledThinkingMode(string $model): ?ThinkingModeEnum
    {
        if (self::supportsThinking($model) && self::matches($model, self::THINKING_BETWEEN_TOOLS)) {
            return ThinkingModeEnum::BETWEEN_TOOLS;
        }

        return self::supportsDisabledThinking($model) ? ThinkingModeEnum::DISABLED : null;
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
