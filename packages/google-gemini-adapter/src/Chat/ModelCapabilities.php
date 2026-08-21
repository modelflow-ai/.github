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

namespace ModelflowAi\GoogleGeminiAdapter\Chat;

/**
 * Thinking support across the Gemini generations.
 *
 * @see https://ai.google.dev/gemini-api/docs/thinking
 */
final class ModelCapabilities
{
    /**
     * Models taking the `thinkingLevel` enum. Gemini 2.x expects a `thinkingBudget` in tokens
     * instead, which this adapter does not send.
     *
     * @var list<string>
     */
    private const THINKING_LEVEL_SUPPORTED = [
        'gemini-3',
    ];

    public static function supportsThinkingLevel(string $model): bool
    {
        // Both the bare id and the fully qualified "models/gemini-3.7-flash" form are in use.
        $name = \str_starts_with($model, 'models/') ? \substr($model, 7) : $model;

        foreach (self::THINKING_LEVEL_SUPPORTED as $candidate) {
            // Covers the generation and its releases, such as "gemini-3.7-flash".
            if ($name === $candidate || \str_starts_with($name, $candidate . '-') || \str_starts_with($name, $candidate . '.')) {
                return true;
            }
        }

        return false;
    }
}
