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
use ModelflowAi\Core\Response\AITextResponse;

class AITextRequest extends AIRequest implements AIRequestInterface
{
    public function __construct(
        protected readonly string $text,
        AIRequestCriteriaCollection $criteria,
        callable $requestHandler,
    ) {
        parent::__construct($criteria, $requestHandler);
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function execute(): AITextResponse
    {
        return \call_user_func($this->requestHandler, $this);
    }
}
