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

namespace ModelflowAi\Embeddings\Generator;

use ModelflowAi\Embeddings\Model\EmbeddingInterface;

interface EmbeddingGeneratorInterface
{
    /**
     * @return EmbeddingInterface[]
     */
    public function embed(EmbeddingInterface $embedding, ?callable $headerGenerator = null): array;
}
