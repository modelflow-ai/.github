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

namespace ModelflowAi\Chat\Response;

/**
 * Estimates token counts for providers that don't provide native usage data.
 *
 * Uses a simplified algorithm based on common tokenization patterns.
 * Note: This is an approximation and may not match the exact token count
 * used by the model provider.
 */
final class TokenEstimator
{
    /**
     * Estimate the number of tokens in the given text.
     *
     * This uses a simplified heuristic:
     * - Split on whitespace and punctuation
     * - Average ~4 characters per token for English text
     * - Add overhead for special characters and formatting
     */
    public static function estimateTokens(string $text): int
    {
        if ('' === $text) {
            return 0;
        }

        // Simple heuristic: ~4 characters per token on average
        // This is based on OpenAI's tokenization patterns
        $charCount = \mb_strlen($text);
        $estimatedTokens = (int) \ceil($charCount / 4.0);

        // Add a small overhead for special characters and punctuation
        $specialChars = \preg_match_all('/[^\w\s]/u', $text);
        $estimatedTokens += (int) ($specialChars * 0.5);

        // Add overhead for newlines and formatting
        $newlines = \substr_count($text, "\n");
        $estimatedTokens += $newlines;

        return \max(1, $estimatedTokens);
    }

    /**
     * Estimate tokens for a chat message.
     *
     * Takes into account the message structure overhead (role, formatting, etc.)
     */
    public static function estimateMessageTokens(AIChatResponseMessage $message): int
    {
        $tokens = 0;

        // Add tokens for role (approximately 1 token)
        ++$tokens;

        if (null !== $message->content) {
            $tokens += self::estimateTokens($message->content);
        }

        if (null !== $message->toolCalls) {
            foreach ($message->toolCalls as $toolCall) {
                // Tool call overhead (~3 tokens for structure)
                $tokens += 3;
                // Tool name
                $tokens += self::estimateTokens($toolCall->name);
                // Tool arguments (as JSON string)
                $tokens += self::estimateTokens(\json_encode($toolCall->arguments, \JSON_THROW_ON_ERROR));
            }
        }

        // Add message framing overhead (~4 tokens per message)
        $tokens += 4;

        return $tokens;
    }
}
