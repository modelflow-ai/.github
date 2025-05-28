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

echo "=== Simple Ollama Completion Example ===\n\n";

// Simple completion example
$prompt = 'Write a haiku about programming:';

echo "Prompt: \"{$prompt}\"\n\n";

$response = $completionHandler->createRequest($prompt)
    ->build()
    ->execute();

echo "Completion:\n{$response->getContent()}\n\n";

echo "=== Example completed successfully! ===\n";
