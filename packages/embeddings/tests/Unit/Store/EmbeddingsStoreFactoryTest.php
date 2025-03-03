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

namespace ModelflowAi\Embeddings\Tests\Unit\Store;

use ModelflowAi\Embeddings\Store\Dsn\Dsn;
use ModelflowAi\Embeddings\Store\EmbeddingsStoreFactory;
use ModelflowAi\Embeddings\Store\EmbeddingsStoreFactoryInterface;
use ModelflowAi\Embeddings\Store\EmbeddingsStoreInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class EmbeddingsStoreFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testCreateWithSupportedDsn(): void
    {
        $dsn = new Dsn('test', 'localhost');

        /** @var ObjectProphecy|EmbeddingsStoreInterface $store */
        $store = $this->prophesize(EmbeddingsStoreInterface::class);

        /** @var ObjectProphecy<EmbeddingsStoreFactoryInterface> $supportedFactory */
        $supportedFactory = $this->prophesize(EmbeddingsStoreFactoryInterface::class);
        $supportedFactory->supports($dsn)->willReturn(true);
        $supportedFactory->create($dsn)->willReturn($store->reveal());

        /** @var ObjectProphecy<EmbeddingsStoreFactoryInterface> $unsupportedFactory */
        $unsupportedFactory = $this->prophesize(EmbeddingsStoreFactoryInterface::class);
        $unsupportedFactory->supports($dsn)->willReturn(false);
        $unsupportedFactory->create($dsn)->shouldNotBeCalled();

        $factory = new EmbeddingsStoreFactory([
            $unsupportedFactory->reveal(),
            $supportedFactory->reveal(),
        ]);

        $result = $factory->create($dsn);

        $this->assertSame($store->reveal(), $result);
    }

    public function testCreateWithUnsupportedDsn(): void
    {
        $dsn = new Dsn('unsupported', 'localhost');

        /** @var ObjectProphecy<EmbeddingsStoreFactoryInterface> $factory1 */
        $factory1 = $this->prophesize(EmbeddingsStoreFactoryInterface::class);
        $factory1->supports($dsn)->willReturn(false);

        /** @var ObjectProphecy<EmbeddingsStoreFactoryInterface> $factory2 */
        $factory2 = $this->prophesize(EmbeddingsStoreFactoryInterface::class);
        $factory2->supports($dsn)->willReturn(false);

        $factory = new EmbeddingsStoreFactory([
            $factory1->reveal(),
            $factory2->reveal(),
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No store supports the given DSN with scheme "unsupported".');

        $factory->create($dsn);
    }

    public function testSupportsWithSupportedDsn(): void
    {
        $dsn = new Dsn('test', 'localhost');

        /** @var ObjectProphecy<EmbeddingsStoreFactoryInterface> $supportedFactory */
        $supportedFactory = $this->prophesize(EmbeddingsStoreFactoryInterface::class);
        $supportedFactory->supports($dsn)->willReturn(true);

        /** @var ObjectProphecy<EmbeddingsStoreFactoryInterface> $unsupportedFactory */
        $unsupportedFactory = $this->prophesize(EmbeddingsStoreFactoryInterface::class);
        $unsupportedFactory->supports($dsn)->willReturn(false);

        $factory = new EmbeddingsStoreFactory([
            $unsupportedFactory->reveal(),
            $supportedFactory->reveal(),
        ]);

        $this->assertTrue($factory->supports($dsn));
    }

    public function testSupportsWithUnsupportedDsn(): void
    {
        $dsn = new Dsn('unsupported', 'localhost');

        /** @var ObjectProphecy<EmbeddingsStoreFactoryInterface> $factory1 */
        $factory1 = $this->prophesize(EmbeddingsStoreFactoryInterface::class);
        $factory1->supports($dsn)->willReturn(false);

        /** @var ObjectProphecy<EmbeddingsStoreFactoryInterface> $factory2 */
        $factory2 = $this->prophesize(EmbeddingsStoreFactoryInterface::class);
        $factory2->supports($dsn)->willReturn(false);

        $factory = new EmbeddingsStoreFactory([
            $factory1->reveal(),
            $factory2->reveal(),
        ]);

        $this->assertFalse($factory->supports($dsn));
    }

    public function testCreateWithEmptyFactories(): void
    {
        $dsn = new Dsn('test', 'localhost');

        $factory = new EmbeddingsStoreFactory([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No store supports the given DSN with scheme "test".');

        $factory->create($dsn);
    }

    public function testSupportsWithEmptyFactories(): void
    {
        $dsn = new Dsn('test', 'localhost');

        $factory = new EmbeddingsStoreFactory([]);

        $this->assertFalse($factory->supports($dsn));
    }

    public function testCreateWithSingleUnsupportedFactory(): void
    {
        $dsn = new Dsn('test', 'localhost');

        /** @var ObjectProphecy<EmbeddingsStoreFactoryInterface> $factory */
        $factory = $this->prophesize(EmbeddingsStoreFactoryInterface::class);
        $factory->supports($dsn)->willReturn(false);

        $factoryObj = new EmbeddingsStoreFactory([$factory->reveal()]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No store supports the given DSN with scheme "test".');

        $factoryObj->create($dsn);
    }
}
