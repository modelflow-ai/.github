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

$response = $client->completion()->create([
    'model' => 'llama2',
    'prompt' => 'Hello world!',
]);

echo $response->response . \PHP_EOL;
