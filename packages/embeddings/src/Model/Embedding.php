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

namespace ModelflowAi\Embeddings\Model;

final class Embedding implements EmbeddingInterface
{
    use EmbeddingTrait;

    public function __construct(
        string $content,
        private readonly string $identifier,
    ) {
        $this->content = $content;
        $this->hash = $this->hash($this->identifier);
    }

    /**
     * @return string[]
     */
    public function getIdentifierParts(): array
    {
        return [$this->identifier];
    }
}
