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

namespace ModelflowAi\Core\Response;

use ModelflowAi\Core\Request\AIRequestInterface;

readonly class AIChatToolResponse implements AIResponseInterface
{
    /**
     * @param AIChatToolCall[] $toolCalls
     */
    public function __construct(
        private AIRequestInterface $request,
        private array $toolCalls,
    ) {
    }

    public function getRequest(): AIRequestInterface
    {
        return $this->request;
    }

    /**
     * @return AIChatToolCall[]
     */
    public function getToolCalls(): array
    {
        return $this->toolCalls;
    }
}
