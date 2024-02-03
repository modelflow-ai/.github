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
use ModelflowAi\Mistral\Mistral;
use PHPUnit\Framework\TestCase;

class MistralTest extends TestCase
{
    public function testClient(): void
    {
        $this->assertInstanceOf(ClientInterface::class, Mistral::client('api-key'));
    }

    public function testFactory(): void
    {
        $this->assertInstanceOf(Factory::class, Mistral::factory());
    }
}
