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

namespace ModelflowAi\Ollama\Tests\Unit;

use ModelflowAi\Ollama\ClientInterface;
use ModelflowAi\Ollama\Factory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testWithHttpClient(): void
    {
        $factory = new Factory();
        $httpClient = $this->prophesize(HttpClientInterface::class);

        $this->assertInstanceOf(
            Factory::class,
            $factory->withHttpClient($httpClient->reveal()),
        );
    }

    public function testWithBaseUrl(): void
    {
        $factory = new Factory();

        $this->assertInstanceOf(
            Factory::class,
            $factory->withBaseUrl('http://localhost:11434/api/'),
        );
    }

    public function testMake(): void
    {
        $factory = new Factory();

        $this->assertInstanceOf(
            ClientInterface::class,
            $factory->make(),
        );
    }
}
