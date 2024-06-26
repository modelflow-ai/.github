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

namespace ModelflowAi\Stability;

use ModelflowAi\Stability\Resources\GenerateUltraInterface;

interface ClientInterface
{
    /**
     * Generate an image by using Stable Image Ultra model.
     *
     * @see https://platform.stability.ai/docs/api-reference#tag/Generate/paths/~1v2beta~1stable-image~1generate~1ultra/post
     */
    public function generateUltra(): GenerateUltraInterface;
}
