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

namespace ModelflowAi\Completion\Request;

use ModelflowAi\Completion\Response\AICompletionResponse;
use ModelflowAi\DecisionTree\Behaviour\CriteriaBehaviour;
use ModelflowAi\DecisionTree\Criteria\CriteriaCollection;

class AICompletionRequest implements CriteriaBehaviour
{
    /**
     * @var callable
     */
    protected $requestHandler;

    /**
     * @param array{
     *     streamed?: bool,
     *     format?: "json"|null,
     * } $options
     */
    public function __construct(
        protected readonly string $prompt,
        private readonly CriteriaCollection $criteria,
        private readonly array $options,
        callable $requestHandler,
    ) {
        $this->requestHandler = $requestHandler;
    }

    /**
     * @param "format"|"streamed" $key
     */
    public function getOption(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }

    public function matches(array $criteria): bool
    {
        return $this->criteria->matches($criteria);
    }

    public function getPrompt(): string
    {
        return $this->prompt;
    }

    public function execute(): AICompletionResponse
    {
        return \call_user_func($this->requestHandler, $this);
    }
}
