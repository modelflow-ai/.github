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

use ModelflowAi\Ollama\Ollama;

require_once __DIR__ . '/../vendor/autoload.php';

$client = Ollama::client();

$response = $client->chat()->create([
    'model' => 'llama2',
    'messages' => [
        ['role' => 'system', 'content' => 'You are an angry bot!'],
        ['role' => 'user', 'content' => 'Hello world!'],
    ],
]);

echo $response->message->content . \PHP_EOL;
