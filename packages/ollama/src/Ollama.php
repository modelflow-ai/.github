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

namespace ModelflowAi\Ollama;

final class Ollama
{
    private function __construct()
    {
    }

    public static function client(): ClientInterface
    {
        return self::factory()->make();
    }

    public static function factory(): Factory
    {
        return new Factory();
    }
}
