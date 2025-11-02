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

namespace ModelflowAi\StabilityAdapter\Image;

use ModelflowAi\Image\Adapter\AIImageAdapterFactoryInterface;
use ModelflowAi\Image\Adapter\AIImageAdapterInterface;
use ModelflowAi\Stability\ClientInterface;

final readonly class StabilityImageAdapterFactory implements AIImageAdapterFactoryInterface
{
    public function __construct(
        private ClientInterface $client,
    ) {
    }

    public function createImageAdapter(array $options): AIImageAdapterInterface
    {
        return new StabilityImageAdapter($this->client);
    }
}
