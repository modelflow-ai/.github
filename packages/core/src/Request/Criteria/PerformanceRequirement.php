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

namespace ModelflowAi\Core\Request\Criteria;

enum PerformanceRequirement: int implements AiCriteriaInterface
{
    case SMART = 2;
    case FAST = 1;

    public function matches(AiCriteriaInterface $toMatch): bool
    {
        if (!$toMatch instanceof self) {
            return true;
        }

        return $this->value >= $toMatch->value;
    }
}
