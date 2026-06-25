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
$document = $argv[1] ?? 'https://arxiv.org/pdf/2201.04234';

// The Mistral OCR API requires the document to be an https URL or a base64
// data URI. A local file path must be read and encoded before sending.
if (\is_file($document)) {
    $contents = \file_get_contents($document);
    if (false === $contents) {
        throw new RuntimeException(\sprintf('Unable to read file "%s".', $document));
    }

    $mimeType = \mime_content_type($document) ?: 'application/pdf';
    $documentUrl = \sprintf('data:%s;base64,%s', $mimeType, \base64_encode($contents));
} else {
    $documentUrl = $document;
}

// Comma-separated, zero-based page indexes (e.g. "0,1,2"). Defaults to the first page.
$pages = $argv[2] ?? '0';

$response = $client->ocr()->process([
    'model' => Model::OCR->value,
    'document' => [
        'type' => 'document_url',
        'document_url' => $documentUrl,
    ],
    'pages' => $pages,
    // Pull header/footer into separate fields so $page->markdown is the main content only.
    'extract_header' => true,
    'extract_footer' => true,
]);

foreach ($response->pages as $page) {
    echo \sprintf("=== Page %d ===\n", $page->index);

    if (null !== $page->header) {
        echo '--- Header ---' . \PHP_EOL;
        echo $page->header . \PHP_EOL;
    }

    echo '--- Content ---' . \PHP_EOL;
    echo $page->markdown . \PHP_EOL;

    if (null !== $page->footer) {
        echo '--- Footer ---' . \PHP_EOL;
        echo $page->footer . \PHP_EOL;
    }
}
