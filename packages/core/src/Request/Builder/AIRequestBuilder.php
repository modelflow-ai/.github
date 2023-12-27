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

namespace ModelflowAi\Core\Request\Builder;

use ModelflowAi\Core\Request\AIRequest;
use ModelflowAi\Core\Request\Criteria\AiCriteriaInterface;
use ModelflowAi\Core\Request\Criteria\AIRequestCriteria;

abstract class AIRequestBuilder
{
    protected AIRequestCriteria $criteria;

    /**
     * @var callable
     */
    protected $requestHandler;

    public function __construct(
        callable $requestHandler,
    ) {
        $this->requestHandler = $requestHandler;

        $this->criteria = new AIRequestCriteria();
    }

    public function addCriteria(AiCriteriaInterface $criteria): self
    {
        $this->criteria = new AIRequestCriteria(
            $this->criteria->criteria + [$criteria],
        );

        return $this;
    }

    public abstract function build(): AIRequest;
}
