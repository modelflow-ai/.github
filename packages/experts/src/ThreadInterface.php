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
use ModelflowAi\Core\Response\AIChatResponse;

interface ThreadInterface
{
    public function addContext(string $key, string $data): self;

    public function run(): AIChatResponse;
}
