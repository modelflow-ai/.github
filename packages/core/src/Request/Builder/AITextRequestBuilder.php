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

use ModelflowAi\Core\Request\AITextRequest;

class AITextRequestBuilder extends AIRequestBuilder
{
    private ?string $text = null;

    public static function create(callable $requestHandler): self
    {
        return new self($requestHandler);
    }

    public function text(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function build(): AITextRequest
    {
        return new AITextRequest(
            $this->text,
            $this->criteria,
            $this->requestHandler,
        );
    }
}
