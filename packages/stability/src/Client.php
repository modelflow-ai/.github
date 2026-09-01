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

use ModelflowAi\ApiClient\Transport\TransportInterface;
use ModelflowAi\Stability\Resources\GenerateUltra;
use ModelflowAi\Stability\Resources\GenerateUltraInterface;

final readonly class Client implements ClientInterface
{
    public function __construct(
        private TransportInterface $transport,
    ) {
    }

    public function generateUltra(): GenerateUltraInterface
    {
        return new GenerateUltra($this->transport);
    }
}
