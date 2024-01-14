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

use ModelflowAi\Ollama\Responses\Completion\CreateResponse;

interface CompletionInterface
{
    /**
     * @param array{
     *     model: string,
     *     prompt: string,
     *     format?: "json",
     *     options?: array<string, string|int|float>,
     *     template?: string,
     *     context?: float[],
     * } $parameters
     */
    public function create(array $parameters): CreateResponse;
}
