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

namespace ModelflowAi\Embeddings\Adapter\Request;

final readonly class EmbedRequest
{
    /**
     * @param string[] $texts Array of texts for batch processing (always array, never empty)
     */
    public function __construct(
        private array $texts,
    ) {
        if ([] === $texts) {
            throw new \InvalidArgumentException('EmbedRequest requires at least one text to embed.');
        }
    }

    /**
     * @return string[]
     */
    public function getTexts(): array
    {
        return $this->texts;
    }

    public function count(): int
    {
        return \count($this->texts);
    }
}
