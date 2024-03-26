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

namespace ModelflowAi\ApiClient\Tests\Exceptions;

use ModelflowAi\ApiClient\Exceptions\TransportException;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Contracts\HttpClient\ResponseInterface;

class TransportExceptionTest extends TestCase
{
    use ProphecyTrait;

    public function testGetResponse(): void
    {
        $response = $this->prophesize(ResponseInterface::class);
        $response->getContent(false)->willReturn('{"message": "Test"}');
        $exception = new TransportException($response->reveal());

        $this->assertSame($response->reveal(), $exception->response);
    }

    public function testGetMessage(): void
    {
        $response = $this->prophesize(ResponseInterface::class);
        $response->getContent(false)->willReturn('{"message": "Test"}');

        $exception = new TransportException($response->reveal());

        $this->assertSame(
            <<<'MESSAGE'
Request fails because of following reasons:
{"message": "Test"}
MESSAGE,
            $exception->getMessage(),
        );
    }
}
