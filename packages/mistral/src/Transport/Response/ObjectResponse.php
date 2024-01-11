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

namespace ModelflowAi\Mistral\Transport\Response;

use ModelflowAi\Mistral\Responses\MetaInformation;

readonly class ObjectResponse extends Response
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        public array $data,
        MetaInformation $meta,
    ) {
        parent::__construct($meta);
    }
}
