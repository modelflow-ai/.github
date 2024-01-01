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

namespace ModelflowAi\Core\Request;

use ModelflowAi\Core\Request\Criteria\AIRequestCriteria;
use ModelflowAi\Core\Request\Criteria\PerformanceRequirement;
use ModelflowAi\Core\Request\Criteria\PrivacyRequirement;
use ModelflowAi\Core\Response\AIResponseInterface;

abstract class AIRequest implements AIRequestInterface
{
    /**
     * @var callable
     */
    protected $requestHandler;

    public function __construct(
        private readonly AIRequestCriteria $criteria,
        callable $requestHandler,
    ) {
        $this->requestHandler = $requestHandler;
    }

    public function getCriteria(): AIRequestCriteria
    {
        return $this->criteria;
    }

    public function matches(PrivacyRequirement|PerformanceRequirement $criteria): bool
    {
        return $this->criteria->matches($criteria);
    }

    abstract public function execute(): AIResponseInterface;
}
