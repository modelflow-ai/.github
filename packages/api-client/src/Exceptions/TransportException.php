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

namespace ModelflowAi\ApiClient\Exceptions;

use Symfony\Contracts\HttpClient\ResponseInterface;

class TransportException extends \RuntimeException
{
    public function __construct(
        public ResponseInterface $response,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            \sprintf("Request fails because of following reasons:\n%s", $this->response->getContent(false)),
            $code,
            $previous,
        );
    }
}
