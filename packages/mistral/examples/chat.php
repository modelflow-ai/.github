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

use ModelflowAi\Mistral\Client;
use ModelflowAi\Mistral\Transport\SymfonyHttpTransporter;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

require_once __DIR__ . '/../vendor/autoload.php';

(new Dotenv())->bootEnv(__DIR__ . '/.env');

$transporter = new SymfonyHttpTransporter(HttpClient::create(), 'https://api.mistral.ai/v1/', [
    'Authorization' => 'Bearer ' . $_SERVER['MISTRAL_API_KEY'],
]);

$client = new Client($transporter);

$response = $client->chat()->create([
    'model' => 'mistral-tiny',
    'messages' => [
        ['role' => 'system', 'content' => 'You are an angry bot!'],
        ['role' => 'user', 'content' => 'Hello world!'],
    ],
]);

echo $response->choices[0]->message->content . \PHP_EOL;
