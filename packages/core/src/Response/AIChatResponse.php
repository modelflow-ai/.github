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

readonly class AIChatResponse implements AIResponseInterface
{
    public function __construct(
        private AIRequestInterface $request,
        private AIChatResponseMessage $message,
    ) {
    }

    public function getRequest(): AIRequestInterface
    {
        return $this->request;
    }

    public function getMessage(): AIChatResponseMessage
    {
        return $this->message;
    }
}
