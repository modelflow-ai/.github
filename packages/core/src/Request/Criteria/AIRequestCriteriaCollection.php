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

readonly class AIRequestCriteriaCollection
{
    /**
     * @param AiCriteriaInterface[] $criteria
     */
    public function __construct(
        public array $criteria = [],
    ) {
    }

    public function matches(AiCriteriaInterface $toMatch): bool
    {
        foreach ($this->criteria as $criteria) {
            if (!$criteria->matches($toMatch)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param FeatureCriteria[] $features
     */
    public function withFeatures(array $features): self
    {
        return new self(\array_merge($this->criteria, $features));
    }
}
