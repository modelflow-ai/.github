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

enum CapabilityCriteria: int implements AiCriteriaInterface
{
    case SMART = 8;
    case ADVANCED = 4;
    case INTERMEDIATE = 2;
    case BASIC = 1;

    public function matches(AiCriteriaInterface $toMatch): bool
    {
        if (!$toMatch instanceof self) {
            return true;
        }

        return $this->value >= $toMatch->value;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
