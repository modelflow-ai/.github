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
 * The two levels the hybrid models accept. Unlike the graded scales of other providers this is a
 * switch: `high` returns a thinking chunk before the answer, `none` leaves it out.
 *
 * @see https://docs.mistral.ai/capabilities/reasoning
 */
enum ReasoningEffortEnum: string
{
    case NONE = 'none';
    case HIGH = 'high';
}
