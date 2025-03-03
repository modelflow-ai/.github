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

namespace ModelflowAi\Embeddings\Tests\Unit\Store\Dsn;

use ModelflowAi\Embeddings\Store\Dsn\Dsn;
use PHPUnit\Framework\TestCase;

class DsnTest extends TestCase
{
    public function testConstruct(): void
    {
        $dsn = new Dsn('test', 'localhost', 'user', 'password', 1234, 'path', ['option1' => 'value1']);

        $this->assertSame('test', $dsn->scheme);
        $this->assertSame('localhost', $dsn->host);
        $this->assertSame('user', $dsn->user);
        $this->assertSame('password', $dsn->password);
        $this->assertSame(1234, $dsn->port);
        $this->assertSame('path', $dsn->path);
        $this->assertSame(['option1' => 'value1'], $dsn->options);
    }

    public function testFromStringWithFilePath(): void
    {
        $dsnString = 'file://%kernel.project_dir%/var/data';

        $dsn = Dsn::fromString($dsnString);

        $this->assertSame('file', $dsn->scheme);
        $this->assertNull($dsn->host);
        $this->assertNull($dsn->user);
        $this->assertNull($dsn->password);
        $this->assertNull($dsn->port);
        $this->assertSame('%kernel.project_dir%/var/data', $dsn->path);
        $this->assertEmpty($dsn->options);
    }

    public function testFromStringWithFilePathAndOptions(): void
    {
        $dsnString = 'file://%kernel.project_dir%/var/data?option1=value1&option2=value2';

        $dsn = Dsn::fromString($dsnString);

        $this->assertSame('file', $dsn->scheme);
        $this->assertNull($dsn->host);
        $this->assertNull($dsn->user);
        $this->assertNull($dsn->password);
        $this->assertNull($dsn->port);
        $this->assertSame('%kernel.project_dir%/var/data', $dsn->path);
        $this->assertSame(['option1' => 'value1', 'option2' => 'value2'], $dsn->options);
    }

    public function testFromStringWithMemory(): void
    {
        $dsnString = 'memory://';

        $dsn = Dsn::fromString($dsnString);

        $this->assertSame('memory', $dsn->scheme);
        $this->assertNull($dsn->host);
        $this->assertNull($dsn->user);
        $this->assertNull($dsn->password);
        $this->assertNull($dsn->port);
        $this->assertNull($dsn->path);
        $this->assertEmpty($dsn->options);
    }

    public function testFromStringWithQdrant(): void
    {
        $dsnString = 'qdrant://admin:password@127.0.0.1:6333/test?tls=true&secret=123456';

        $dsn = Dsn::fromString($dsnString);

        $this->assertSame('qdrant', $dsn->scheme);
        $this->assertSame('127.0.0.1', $dsn->host);
        $this->assertSame('admin', $dsn->user);
        $this->assertSame('password', $dsn->password);
        $this->assertSame(6333, $dsn->port);
        $this->assertSame('test', $dsn->path);
        $this->assertSame(['tls' => 'true', 'secret' => '123456'], $dsn->options);
    }

    public function testFromStringWithSpecialCharactersInCredentials(): void
    {
        $dsnString = 'qdrant://user%40domain:p%40ssw%3Ard@localhost:6333/test';

        $dsn = Dsn::fromString($dsnString);

        $this->assertSame('user@domain', $dsn->user);
        $this->assertSame('p@ssw:rd', $dsn->password);
    }

    public function testFromStringWithSpecialCharacterPlus(): void
    {
        $dsnString = 'qdrant://user+name:pass+word@localhost:6333/test';

        $dsn = Dsn::fromString($dsnString);

        $this->assertSame('user+name', $dsn->user);
        $this->assertSame('pass+word', $dsn->password);
    }

    public function testFromStringWithNestedArrayOption(): void
    {
        $dsnString = 'qdrant://localhost/test?option[key1]=value1&option[key2]=value2';

        $dsn = Dsn::fromString($dsnString);

        $this->assertSame(['option' => ['key1' => 'value1', 'key2' => 'value2']], $dsn->options);
    }

    public function testFromStringWithoutScheme(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The DSN must contain a scheme.');

        Dsn::fromString('localhost');
    }

    public function testFromStringWithInvalidDsn(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The DSN must contain a scheme.');

        Dsn::fromString(':invalid');
    }

    public function testFromStringWithoutHostForNonFileScheme(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The DSN is invalid.');

        Dsn::fromString('redis:///db');
    }

    public function testGetOption(): void
    {
        $dsn = new Dsn('test', 'localhost', options: ['option1' => 'value1', 'option2' => 'value2']);

        $this->assertSame('value1', $dsn->getOption('option1'));
        $this->assertSame('value2', $dsn->getOption('option2'));
        $this->assertNull($dsn->getOption('nonexistent'));
        $this->assertSame('default', $dsn->getOption('nonexistent', 'default'));
    }

    public function testGetBooleanOption(): void
    {
        $dsn = new Dsn('test', 'localhost', options: [
            'true1' => true,
            'true2' => 'true',
            'true3' => '1',
            'true4' => 1,
            'false1' => false,
            'false2' => 'false',
            'false3' => '0',
            'false4' => 0,
        ]);

        $this->assertTrue($dsn->getBooleanOption('true1'));
        $this->assertTrue($dsn->getBooleanOption('true2'));
        $this->assertTrue($dsn->getBooleanOption('true3'));
        $this->assertTrue($dsn->getBooleanOption('true4'));
        $this->assertFalse($dsn->getBooleanOption('false1'));
        $this->assertFalse($dsn->getBooleanOption('false2'));
        $this->assertFalse($dsn->getBooleanOption('false3'));
        $this->assertFalse($dsn->getBooleanOption('false4'));
        $this->assertFalse($dsn->getBooleanOption('nonexistent'));
        $this->assertTrue($dsn->getBooleanOption('nonexistent', true));
    }

    public function testFromStringWithArrayOption(): void
    {
        $dsnString = 'qdrant://localhost/test?option[]=value1&option[]=value2';

        $dsn = Dsn::fromString($dsnString);

        $this->assertSame(['option' => ['value1', 'value2']], $dsn->options);
    }

    public function testFromStringWithEmptyPath(): void
    {
        $dsnString = 'redis://localhost/';

        $dsn = Dsn::fromString($dsnString);

        $this->assertEmpty($dsn->path);
    }

    public function testFromStringWithEmptyUser(): void
    {
        $dsnString = 'redis://:password@localhost';

        $dsn = Dsn::fromString($dsnString);

        $this->assertNull($dsn->user);
        $this->assertSame('password', $dsn->password);
    }
}
