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

use ModelflowAi\Core\Request\Message\AIChatMessageRoleEnum;

readonly class AIChatResponseMessage
{
    public function __construct(
        public AIChatMessageRoleEnum $role,
        public string $content,
    ) {
    }
}
