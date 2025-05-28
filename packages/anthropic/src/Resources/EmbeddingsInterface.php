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

namespace ModelflowAi\Anthropic\Resources;

use ModelflowAi\Anthropic\Responses\Embeddings\CreateResponse;

interface EmbeddingsInterface
{
    /**
     * @param array{
     *     input: string|array<string>,
     *     model: string,
     *     input_type?: string,
     *     truncation?: bool,
     * } $parameters
     */
    public function create(array $parameters): CreateResponse;
}
