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

namespace ModelflowAi\Embeddings\Tests\Unit\Store\Filesystem;

use ModelflowAi\Embeddings\Store\Dsn\Dsn;
use ModelflowAi\Embeddings\Store\Dsn\InvalidDsnException;
use ModelflowAi\Embeddings\Store\Filesystem\FilesystemEmbeddingsStore;
use ModelflowAi\Embeddings\Store\Filesystem\FilesystemEmbeddingsStoreFactory;
use PHPUnit\Framework\TestCase;

class FilesystemEmbeddingsStoreFactoryTest extends TestCase
{
    private FilesystemEmbeddingsStoreFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new FilesystemEmbeddingsStoreFactory();
    }

    public function testSupportsFileScheme(): void
    {
        $dsn = new Dsn('file', null, null, null, null, '/path/to/file.store');

        $this->assertTrue($this->factory->supports($dsn));
    }

    public function testDoesNotSupportOtherSchemes(): void
    {
        $schemes = ['memory', 'qdrant', 'redis', 'http', 'ftp'];

        foreach ($schemes as $scheme) {
            $dsn = new Dsn($scheme, 'localhost');
            $this->assertFalse($this->factory->supports($dsn));
        }
    }

    public function testCreateWithValidPath(): void
    {
        $path = __DIR__ . '/file.store';
        $dsn = new Dsn('file', null, null, null, null, $path);

        $store = $this->factory->create($dsn);

        $this->assertInstanceOf(FilesystemEmbeddingsStore::class, $store);
        $this->assertSame($path, $this->getPrivateProperty($store, 'filePath'));
    }

    public function testCreateWithNullPath(): void
    {
        $dsn = new Dsn('file', null);

        $this->expectException(InvalidDsnException::class);
        $this->expectExceptionMessage('The path must be a string for file scheme DSNs.');

        $this->factory->create($dsn);
    }

    public function testFromDsnStringWithOptions(): void
    {
        $dsnString = 'file://' . __DIR__ . '/embeddings.store?option=value';
        $dsn = Dsn::fromString($dsnString);

        $store = $this->factory->create($dsn);

        $this->assertInstanceOf(FilesystemEmbeddingsStore::class, $store);
        $this->assertSame(__DIR__ . '/embeddings.store', $this->getPrivateProperty($store, 'filePath'));
    }

    /**
     * Helper method to access private properties of an object.
     */
    private function getPrivateProperty(object $object, string $propertyName): mixed
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
}
