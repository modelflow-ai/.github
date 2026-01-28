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

namespace ModelflowAi\DecisionTree\Criteria;

use ModelflowAi\DecisionTree\DecisionEnum;

readonly class CriteriaCollection
{
    /**
     * @param CriteriaInterface[] $all
     */
    public function __construct(
        public array $all = [],
    ) {
    }

    /**
     * @param CriteriaInterface[] $toMatch
     */
    public function matches(array $toMatch): bool
    {
        $matches = [];
        $sameType = [];
        $types = [];
        $toMatchTypes = [];
        foreach ($this->all as $criteria) {
            $types[$criteria::class] ??= 0;
            ++$types[$criteria::class];

            foreach ($toMatch as $toMatchCriteria) {
                $toMatchTypes[$toMatchCriteria::class] = true;

                $decision = $criteria->matches($toMatchCriteria);
                if (DecisionEnum::NO_MATCH === $decision) {
                    return false;
                }
                if (DecisionEnum::SAME_TYPE === $decision) {
                    $sameType[$toMatchCriteria::class] ??= 0;
                    ++$sameType[$toMatchCriteria::class];
                }
                if (DecisionEnum::MATCH === $decision) {
                    $matches[$toMatchCriteria::class] ??= 0;
                    ++$matches[$toMatchCriteria::class];
                }
            }
        }

        foreach ($types as $key => $value) {
            $matchValue = $matches[$key] ?? null;
            if (null === $matchValue && !($toMatchTypes[$key] ?? false)) {
                continue;
            }

            if ($value !== $matchValue) {
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
        return new self(\array_merge($this->all, $features));
    }

    /**
     * @return array<array{
     *     name: string,
     *     value: mixed,
     *     class: class-string<CriteriaInterface>,
     * }>
     */
    public function toArray(): array
    {
        return \array_map(
            static fn (CriteriaInterface $criteria) => [
                'name' => $criteria->getName(),
                'value' => $criteria->getValue(),
                'class' => $criteria::class,
            ],
            $this->all,
        );
    }
}
