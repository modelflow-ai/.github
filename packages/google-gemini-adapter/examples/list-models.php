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

require_once __DIR__ . '/../vendor/autoload.php';

use Http\Discovery\Psr18ClientDiscovery;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(__DIR__ . '/.env');

$apiKey = $_ENV['GOOGLE_GEMINI_API_KEY'];
if (!$apiKey) {
    throw new \RuntimeException('Google Gemini API key is required');
}

$client = Psr18ClientDiscovery::find();

$url = "https://generativelanguage.googleapis.com/v1/models?key={$apiKey}";

$request = new \Nyholm\Psr7\Request('GET', $url);
$response = $client->sendRequest($request);

$body = (string) $response->getBody();
$data = \json_decode($body, true, 512, \JSON_THROW_ON_ERROR);

echo "Available Gemini models that support generateContent:\n\n";
if (isset($data['models'])) {
    foreach ($data['models'] as $model) {
        $name = $model['name'] ?? 'unknown';
        $displayName = $model['displayName'] ?? 'N/A';
        $description = $model['description'] ?? 'N/A';

        $methods = $model['supportedGenerationMethods'] ?? [];
        if (\in_array('generateContent', $methods, true)) {
            echo "✓ {$name}\n";
            echo "  Display: {$displayName}\n";
            echo "  Description: {$description}\n";
            echo "  Methods: " . \implode(', ', $methods) . "\n\n";
        }
    }
} else {
    echo "Error: " . \print_r($data, true) . "\n";
}
