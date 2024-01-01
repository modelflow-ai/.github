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

use ModelflowAi\PromptTemplate\Chat\AIChatMessage;

class AIChatMessageCollection extends \ArrayObject
{
    public function __construct(
        AIChatMessage ...$messages,
    ) {
        parent::__construct($messages);
    }

    public function toArray(): array
    {
        return \array_map(
            fn (AIChatMessage $message) => [
                'role' => $message->role->value,
                'content' => $message->content,
            ],
            $this->getArrayCopy(),
        );
    }
}
