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

namespace ModelflowAi\Embeddings\Tests\Unit\Store\Memory;

use ModelflowAi\Embeddings\Store\Dsn\Dsn;
use ModelflowAi\Embeddings\Store\Memory\MemoryEmbeddingsStore;
use ModelflowAi\Embeddings\Store\Memory\MemoryEmbeddingsStoreFactory;
use PHPUnit\Framework\TestCase;

class MemoryEmbeddingsStoreFactoryTest extends TestCase
{
    private MemoryEmbeddingsStoreFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new MemoryEmbeddingsStoreFactory();
    }

    public function testSupportsMemoryScheme(): void
    {
        $dsn = new Dsn('memory', '');

        $this->assertTrue($this->factory->supports($dsn));
    }

    public function testDoesNotSupportOtherSchemes(): void
    {
        $schemes = ['file', 'qdrant', 'redis', 'http', 'ftp'];

        foreach ($schemes as $scheme) {
            $dsn = new Dsn($scheme, 'localhost');
            $this->assertFalse($this->factory->supports($dsn));
        }
    }

    public function testCreate(): void
    {
        $dsn = new Dsn('memory', '');

        $store = $this->factory->create($dsn);

        $this->assertInstanceOf(MemoryEmbeddingsStore::class, $store);
    }

    public function testCreateIgnoresAllParameters(): void
    {
        // Memory store should ignore all parameters in the DSN
        $dsn = new Dsn('memory', 'localhost', 'user', 'password', 1234, 'path', ['option' => 'value']);

        $store = $this->factory->create($dsn);

        $this->assertInstanceOf(MemoryEmbeddingsStore::class, $store);
    }

    public function testFromDsnString(): void
    {
        $dsnString = 'memory://';
        $dsn = Dsn::fromString($dsnString);

        $store = $this->factory->create($dsn);

        $this->assertInstanceOf(MemoryEmbeddingsStore::class, $store);
    }
}
