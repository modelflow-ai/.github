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

use Webmozart\Assert\Assert;

class AIChatResponseStreamMessageBuilder
{
    /**
     * @param AIChatResponseMessage[] $messages
     */
    public function __construct(
        private array $messages = [],
    ) {
    }

    public function getMessage(): AIChatResponseMessage
    {
        $role = null;
        $contentParts = [];
        foreach ($this->messages as $message) {
            if (null === $role) {
                $role = $message->role;
            }
            $contentParts[] = $message->content;
        }

        Assert::notNull($role, 'There should be at least one message in the stream.');

        return new AIChatResponseMessage($role, \implode('', $contentParts));
    }

    public function add(AIChatResponseMessage $message): void
    {
        $this->messages[] = $message;
    }
}
