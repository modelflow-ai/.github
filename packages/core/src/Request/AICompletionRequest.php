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

use ModelflowAi\Core\Request\Criteria\AIRequestCriteriaCollection;
use ModelflowAi\Core\Response\AICompletionResponse;

class AICompletionRequest extends AIRequest implements AIRequestInterface
{
    public function __construct(
        protected readonly string $prompt,
        AIRequestCriteriaCollection $criteria,
        callable $requestHandler,
    ) {
        parent::__construct($criteria, $requestHandler);
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
