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

$apiKey = $_ENV['MISTRAL_API_KEY'] ?? null;
if (!\is_string($apiKey)) {
    throw new RuntimeException('The MISTRAL_API_KEY environment variable is required.');
}

$client = Mistral::client($apiKey);
$documentUrl = $argv[1] ?? 'https://arxiv.org/pdf/2201.04234';

$response = $client->ocr()->process([
    'model' => Model::OCR->value,
    'document' => [
        'type' => 'document_url',
        'document_url' => $documentUrl,
    ],
    'pages' => '0',
]);

echo $response->pages[0]->markdown . \PHP_EOL;
