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
 * @see https://developers.openai.com/api/docs/guides/reasoning
 */
enum ReasoningEffortEnum: string
{
    case NONE = 'none';
    case MINIMAL = 'minimal';
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case XHIGH = 'xhigh';
    case MAX = 'max';
}
