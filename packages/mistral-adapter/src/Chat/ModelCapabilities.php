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

namespace ModelflowAi\MistralAdapter\Chat;

/**
 * Request surface differences between the hybrid models, which answer with a thinking chunk when
 * asked to, and the rest of the Mistral catalog.
 *
 * @see https://docs.mistral.ai/capabilities/reasoning
 */
final class ModelCapabilities
{
    /**
     * The hybrid models are the only ones that accept `reasoning_effort`; the others reject it.
     * Listed one by one on purpose: the snapshots released before a family became hybrid share its
     * name, so matching the family as a prefix would send the parameter to models that refuse it.
     *
     * @var list<string>
     */
    private const REASONING_EFFORT_SUPPORTED = [
        'mistral-small-latest',
        'mistral-medium-latest',
        'mistral-medium-3-5',
    ];

    public static function supportsReasoningEffort(string $model): bool
    {
        return \in_array($model, self::REASONING_EFFORT_SUPPORTED, true);
    }
}
