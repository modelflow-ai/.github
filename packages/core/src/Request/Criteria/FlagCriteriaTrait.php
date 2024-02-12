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

trait FlagCriteriaTrait
{
    public function matches(AiCriteriaInterface $toMatch): bool
    {
        if (!$toMatch instanceof self) {
            return true;
        }

        return $this->getValue() === $toMatch->getValue();
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
