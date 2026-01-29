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

namespace ModelflowAi\Anthropic\Tests\Unit;

use ModelflowAi\Anthropic\ClientInterface;
use ModelflowAi\Anthropic\Factory;
use ModelflowAi\ApiClient\Transport\TransportInterface;
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
        $factory->withApiKey('api-key');

        $client = $factory->make();
        $baseUrl = $this->getBaseUrlFromClient($client);

        // Default baseUrl
        $this->assertSame('https://api.anthropic.com/v1/', $baseUrl);

        $this->assertInstanceOf(
            Factory::class,
            $factory->withBaseUrl('https://api.anthropic.com/v2/'),
        );

        $client = $factory->make();
        $baseUrl = $this->getBaseUrlFromClient($client);
        $this->assertSame('https://api.anthropic.com/v2/', $baseUrl);
    }

    public function testWithVersion(): void
    {
        $factory = new Factory();
        $factory->withApiKey('api-key');

        $client = $factory->make();
        $headers = $this->getHeadersFromClient($client);

        // Default version
        $this->assertSame('2023-06-01', $headers['anthropic-version']);

        $this->assertInstanceOf(
            Factory::class,
            $factory->withVersion('2024-04-04'),
        );

        $client = $factory->make();
        $headers = $this->getHeadersFromClient($client);

        $this->assertSame('2024-04-04', $headers['anthropic-version']);
    }

    public function testWithBeta(): void
    {
        $factory = new Factory();
        $factory->withApiKey('api-key');

        $client = $factory->make();
        $headers = $this->getHeadersFromClient($client);

        // No beta given by default
        $this->assertArrayNotHasKey('anthropic-beta', $headers);

        $this->assertInstanceOf(
            Factory::class,
            $factory->withBeta('tools-2024-04-04'),
        );

        $client = $factory->make();
        $headers = $this->getHeadersFromClient($client);

        $this->assertSame('tools-2024-04-04', $headers['anthropic-beta']);
    }

    public function testWithApiKey(): void
    {
        $factory = new Factory();

        $this->assertInstanceOf(
            Factory::class,
            $factory->withApiKey('api-key'),
        );

        $client = $factory->make();
        $headers = $this->getHeadersFromClient($client);

        $this->assertSame('api-key', $headers['x-api-key']);
    }

    public function testMake(): void
    {
        $factory = new Factory();

        $this->assertInstanceOf(
            ClientInterface::class,
            $factory->withApiKey('api-key')->make(),
        );
    }

    public function getTransportFromClient(ClientInterface $client): TransportInterface
    {
        $property = new \ReflectionProperty($client, 'transport');

        /** @var TransportInterface $value */
        $value = $property->getValue($client);

        return $value;
    }

    /**
     * @return array<string, mixed>
     */
    private function getHeadersFromClient(ClientInterface $client): array
    {
        $transport = $this->getTransportFromClient($client);

        $property = new \ReflectionProperty($transport, 'headers');

        /** @var array<string, mixed> $value */
        $value = $property->getValue($transport);

        return $value;
    }

    private function getBaseUrlFromClient(ClientInterface $client): string
    {
        $transport = $this->getTransportFromClient($client);

        $property = new \ReflectionProperty($transport, 'baseUrl');

        /** @var string $value */
        $value = $property->getValue($transport);

        return $value;
    }
}
