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

use ModelflowAi\Core\Request\Criteria\AiCriteriaInterface;
use ModelflowAi\Core\Request\Criteria\AIRequestCriteriaCollection;
use ModelflowAi\Core\Response\AIResponseInterface;

abstract class AIRequest implements AIRequestInterface
{
    /**
     * @var callable
     */
    protected $requestHandler;

    /**
     * @param array{
     *     format?: "json"|null
     * } $options
     */
    public function __construct(
        private readonly AIRequestCriteriaCollection $criteria,
        private readonly array $options,
        callable $requestHandler,
    ) {
        $this->requestHandler = $requestHandler;
    }

    /**
     * @param "format" $key
     */
    public function getOption(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }

    public function matches(AiCriteriaInterface $criteria): bool
    {
        return $this->criteria->matches($criteria);
    }

    abstract public function execute(): AIResponseInterface;
}
