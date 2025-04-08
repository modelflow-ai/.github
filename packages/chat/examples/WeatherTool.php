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

/**
 * Example weather tool implementation.
 */
class WeatherTool
{
    /**
     * Get the current weather for a city.
     *
     * @param string $city The city name
     *
     * @return array The weather information
     */
    public function getCurrentWeather(string $city): array
    {
        // In a real application, this would make an API call to a weather service
        // For demo purposes, we'll just return some fake data
        $weatherData = [
            'hohenems' => [
                'temperature' => 22,
                'condition' => 'sunny',
                'humidity' => 45,
                'wind_speed' => 10,
            ],
            'vienna' => [
                'temperature' => 18,
                'condition' => 'partly cloudy',
                'humidity' => 65,
                'wind_speed' => 15,
            ],
            'salzburg' => [
                'temperature' => 16,
                'condition' => 'rainy',
                'humidity' => 80,
                'wind_speed' => 12,
            ],
        ];

        // Convert city name to lowercase for case-insensitive lookup
        $cityLower = \strtolower($city);

        // Return data for the requested city, or default data if not found
        return $weatherData[$cityLower] ?? [
            'temperature' => 20,
            'condition' => 'unknown',
            'humidity' => 60,
            'wind_speed' => 8,
        ];
    }
}
