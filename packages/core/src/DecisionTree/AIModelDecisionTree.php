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

namespace ModelflowAi\Core\DecisionTree;

use ModelflowAi\Core\Model\AIModelAdapterInterface;
use ModelflowAi\Core\Request\AIRequestInterface;

final readonly class AIModelDecisionTree implements AIModelDecisionTreeInterface
{
    /**
     * @param DecisionRuleInterface[] $rules
     */
    public function __construct(private array $rules)
    {
    }

    public function determineAdapter(AIRequestInterface $request): AIModelAdapterInterface
    {
        foreach ($this->rules as $rule) {
            if ($rule->matches($request)) {
                return $rule->getAdapter();
            }
        }

        throw new \Exception('No matching adapter found.');
    }
}
