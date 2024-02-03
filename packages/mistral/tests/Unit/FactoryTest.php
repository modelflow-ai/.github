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

namespace ModelflowAi\Mistral\Tests\Unit;

use ModelflowAi\Mistral\ClientInterface;
use ModelflowAi\Mistral\Factory;
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
            $factory->withBaseUrl('https://api.mistral.ai/v1/'),
        );
    }

    public function testWithApiKey(): void
    {
        $factory = new Factory();

        $this->assertInstanceOf(
            Factory::class,
            $factory->withApiKey('api-key'),
        );
    }

    public function testMake(): void
    {
        $factory = new Factory();

        $this->assertInstanceOf(
            ClientInterface::class,
            $factory->withApiKey('api-key')->make(),
        );
    }
}
