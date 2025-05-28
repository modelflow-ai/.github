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

namespace App;

use ModelflowAi\Embeddings\Model\EmbeddingInterface;
use ModelflowAi\Embeddings\Model\EmbeddingTrait;

class ExampleEmbedding implements EmbeddingInterface
{
    use EmbeddingTrait;

    public function __construct(
        string $content,
        private readonly string $fileName,
        public readonly ?string $category = null,
    ) {
        $this->content = $content;
        $this->hash = $this->hash($fileName);
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @return string[]
     */
    public function getIdentifierParts(): array
    {
        return [$this->fileName];
    }
}
