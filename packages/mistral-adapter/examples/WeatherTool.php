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
     * Can be called foreach location to get the current weather. Also multiple times in one call.
     *
     * @return array{
     *     location: string,
     *     timestamp: int,
     *     weather: string,
     *     temperature: float,
     * }
     */
    public function getCurrentWeather(string $location, ?int $timestamp): array
    {
        $weather = ['rainy', 'sunny', 'cloudy', 'stormy', 'snowy'];

        return [
            'location' => $location,
            'timestamp' => $timestamp ?? \time(),
            'weather' => $weather[\random_int(0, \count($weather) - 1)],
            'temperature' => \random_int(-20, 40) + \random_int(0, 9) / 10.0,
        ];
    }
}
