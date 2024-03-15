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

namespace App;

class WeatherTool
{
    /**
     * @return array{
     *     location: string,
     *     timestamp: int,
     *     weather: string,
     *     temperature: float,
     * }
     */
    public function getCurrentWeather(string $location, ?int $timestamp): array
    {
        return [
            'location' => $location,
            'timestamp' => $timestamp ?? \time(),
            'weather' => 'sunny',
            'temperature' => 22.5,
        ];
    }
}
