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

use ModelflowAi\Mistral\Mistral;
use ModelflowAi\Mistral\Model;
use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

(new Dotenv())->bootEnv(__DIR__ . '/.env');

$client = Mistral::client($_ENV['MISTRAL_API_KEY']);

$responses = $client->chat()->createStreamed([
    'model' => Model::TINY->value,
    'messages' => [
        ['role' => 'system', 'content' => 'You are an angry bot!'],
        ['role' => 'user', 'content' => 'Hello world!'],
    ],
]);

foreach ($responses as $index => $response) {
    if (0 === $index) {
        echo $response->choices[0]->delta->role . ': ';
    }

    echo $response->choices[0]->delta->content;
}
