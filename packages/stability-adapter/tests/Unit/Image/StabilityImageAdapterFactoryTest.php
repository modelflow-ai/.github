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

namespace ModelflowAi\StabilityAdapter\Tests\Unit\Image;

use ModelflowAi\Stability\ClientInterface;
use ModelflowAi\StabilityAdapter\Image\StabilityImageAdapter;
use ModelflowAi\StabilityAdapter\Image\StabilityImageAdapterFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class StabilityImageAdapterFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testCreateImageAdapter(): void
    {
        $client = $this->prophesize(ClientInterface::class);
        $factory = new StabilityImageAdapterFactory($client->reveal());

        // @phpstan-ignore-next-line - Factory doesn't use options for Stability
        $adapter = $factory->createImageAdapter([]);

        $this->assertInstanceOf(StabilityImageAdapter::class, $adapter);
    }
}
