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
use ModelflowAi\Core\Request\Criteria\AIRequestCriteriaCollection;

abstract class AIRequestBuilder
{
    protected AIRequestCriteriaCollection $criteria;

    /**
     * @var array{
     *     format?: "json"|null
     * }
     */
    protected array $options = [];

    /**
     * @var callable
     */
    protected $requestHandler;

    public function __construct(
        callable $requestHandler,
    ) {
        $this->requestHandler = $requestHandler;

        $this->criteria = new AIRequestCriteriaCollection();
    }

    public function asJson(): self
    {
        $this->options['format'] = 'json';

        return $this;
    }

    public function streamed(): self
    {
        $this->options['streamed'] = true;

        return $this;
    }

    /**
     * @param AiCriteriaInterface|AiCriteriaInterface[] $criteria
     */
    public function addCriteria(AiCriteriaInterface|array $criteria): self
    {
        $criteria = \is_array($criteria) ? $criteria : [$criteria];

        $this->criteria = new AIRequestCriteriaCollection(
            \array_merge($this->criteria->criteria, $criteria),
        );

        return $this;
    }

    abstract public function build(): AIRequest;
}
