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

namespace ModelflowAi\Stability\Resources;

use ModelflowAi\Stability\Responses\Generate\GenerateBase64Response;
use ModelflowAi\Stability\Responses\Generate\GenerateFileResponse;

/**
 * @phpstan-type Parameters array{
 *     prompt: string,
 *     negative_prompt?: string,
 *     aspect_ratio?: "16:9"|"1:1"|"21:9"|"3:2"|"2:3"|"4:5"|"5:4"|"9:16"|"9:21",
 *     seed?: string,
 *     output_format?: "jpeg"|"png"|"webp",
 * }
 */
interface GenerateUltraInterface
{
    /**
     * @param Parameters $parameters
     */
    public function generateAsResource(array $parameters): GenerateFileResponse;

    /**
     * @param Parameters $parameters
     */
    public function generateAsBase64(array $parameters): GenerateBase64Response;
}
