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

namespace ModelflowAi\Mistral\Responses\Chat;

/**
 * A model that thinks answers with a list of chunks instead of a string: `thinking` carries the
 * trace, `text` the answer. Streaming mixes both, and the chunk where the trace ends carries the
 * first piece of the answer with it.
 *
 * Only the answer survives here. Carrying the trace any further would mean modelling it all the way
 * through the chat response, which is a question for the chat package rather than this client.
 *
 * @see https://docs.mistral.ai/capabilities/reasoning
 */
final class ContentChunks
{
    /**
     * @param string|list<string|array{type?: string, text?: string, thinking?: mixed}>|null $content
     */
    public static function toText(string|array|null $content): ?string
    {
        if (!\is_array($content)) {
            return $content;
        }

        $text = '';
        foreach ($content as $chunk) {
            if (\is_string($chunk)) {
                $text .= $chunk;

                continue;
            }

            if ('text' === ($chunk['type'] ?? null)) {
                $text .= $chunk['text'] ?? '';
            }
        }

        return $text;
    }
}
