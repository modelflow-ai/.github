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
use ModelflowAi\Core\Response\AIChatResponse;

class AIChatRequest extends AIRequest implements AIRequestInterface
{
    public function __construct(
        private readonly AIChatMessageCollection $messages,
        AIRequestCriteriaCollection $criteria,
        callable $requestHandler,
    ) {
        parent::__construct($criteria, $requestHandler);
    }

    public function getMessages(): AIChatMessageCollection
    {
        return $this->messages;
    }

    public function execute(): AIChatResponse
    {
        return \call_user_func($this->requestHandler, $this);
    }
}
