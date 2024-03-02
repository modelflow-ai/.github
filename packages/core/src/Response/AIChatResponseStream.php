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

readonly class AIChatResponseStream extends AIChatResponse implements AIResponseInterface
{
    private AIChatResponseStreamMessageBuilder $messageBuilder;

    /**
     * @param \Iterator<int, AIChatResponseMessage> $messages
     */
    public function __construct(
        private AIRequestInterface $request,
        private \Iterator $messages,
    ) {
        $this->messageBuilder = new AIChatResponseStreamMessageBuilder();
    }

    public function getRequest(): AIRequestInterface
    {
        return $this->request;
    }

    public function getMessage(): AIChatResponseMessage
    {
        return $this->messageBuilder->getMessage();
    }

    /**
     * @return \Iterator<int, AIChatResponseMessage>
     */
    public function getMessageStream(): \Iterator
    {
        foreach ($this->messages as $message) {
            $this->messageBuilder->add($message);

            yield $message;
        }
    }
}
