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

namespace ModelflowAi\Experts;

use ModelflowAi\Core\AIRequestHandlerInterface;

class ThreadFactory implements ThreadFactoryInterface
{
    public function __construct(
        private readonly AIRequestHandlerInterface $requestHandler,
    ) {
    }

    public function createThread(Expert $expert): Thread
    {
        return new Thread($this->requestHandler, $expert);
    }
}
