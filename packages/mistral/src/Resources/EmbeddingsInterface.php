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

namespace ModelflowAi\Mistral\Resources;

use ModelflowAi\Mistral\Responses\Embeddings\CreateResponse;

interface EmbeddingsInterface
{
    /**
     * @param array{
     *     model?: string,
     *     input: string[],
     * } $parameters
     */
    public function create(array $parameters): CreateResponse;
}
