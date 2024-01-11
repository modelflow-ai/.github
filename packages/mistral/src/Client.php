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

namespace ModelflowAi\Mistral;

use ModelflowAi\Mistral\Resources\Chat;
use ModelflowAi\Mistral\Transport\TransportInterface;

class Client
{
    public function __construct(
        private readonly TransportInterface $transport,
    ) {
    }

    /**
     * Given a chat conversation, the model will return a chat completion response.
     *
     * @see https://docs.mistral.ai/api/#operation/createChatCompletion
     */
    public function chat(): Chat
    {
        return new Chat($this->transport);
    }
}
