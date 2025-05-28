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

/** @var ModelflowAi\Completion\AICompletionRequestHandlerInterface $completionHandler */
$completionHandler = require_once __DIR__ . '/bootstrap.php';

echo "=== Ollama Completion Example ===\n\n";

// Sample prompts for completion
$prompts = [
    'The future of artificial intelligence is',
    'Climate change solutions include',
    'The most important programming concepts are',
];

foreach ($prompts as $index => $prompt) {
    $displayIndex = $index + 1;
    echo "Completion {$displayIndex}: \"{$prompt}\"\n";

    $response = $completionHandler->createRequest($prompt)
        ->build()
        ->execute();

    echo "   📝 Completion: {$response->getContent()}\n";

    echo '   ' . \str_repeat('-', 80) . "\n\n";
}

echo "=== Example completed successfully! ===\n";
