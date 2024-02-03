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
use ModelflowAi\Ollama\Ollama;
use PHPUnit\Framework\TestCase;

class OllamaTest extends TestCase
{
    public function testClient(): void
    {
        $this->assertInstanceOf(ClientInterface::class, Ollama::client());
    }

    public function testFactory(): void
    {
        $this->assertInstanceOf(Factory::class, Ollama::factory());
    }
}
