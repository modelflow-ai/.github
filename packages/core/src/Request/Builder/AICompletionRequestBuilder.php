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

namespace ModelflowAi\Core\Request\Builder;

use ModelflowAi\Core\Request\AICompletionRequest;

class AICompletionRequestBuilder extends AIRequestBuilder
{
    private ?string $prompt = null;

    public static function create(callable $requestHandler): self
    {
        return new self($requestHandler);
    }

    public function prompt(?string $prompt = null): self
    {
        $this->prompt = $prompt;

        return $this;
    }

    public function build(): AICompletionRequest
    {
        if (null === $this->prompt) {
            throw new \RuntimeException('No text given');
        }

        return new AICompletionRequest(
            $this->prompt,
            $this->criteria,
            $this->options,
            $this->requestHandler,
        );
    }
}
