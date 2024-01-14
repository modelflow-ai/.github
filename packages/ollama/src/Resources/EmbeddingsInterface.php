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

namespace ModelflowAi\Ollama\Resources;

use ModelflowAi\Ollama\Responses\Embeddings\CreateResponse;

interface EmbeddingsInterface
{
    /**
     * @param array{
     *     model: string,
     *     prompt: string,
     *     options?: array<string, string|int|float>,
     * } $parameters
     */
    public function create(array $parameters): CreateResponse;
}
