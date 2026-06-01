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

use ModelflowAi\Chat\AIChatRequestHandlerInterface;
use ModelflowAi\Chat\Request\Message\AIChatMessage;
use ModelflowAi\Chat\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\DecisionTree\Criteria\CapabilityCriteria;

/** @var AIChatRequestHandlerInterface $handler */
$handler = require_once __DIR__ . '/bootstrap.php';

// Mistral supports plain JSON mode (json_object) for models that advertise JSON output.
// The response will be valid JSON but without strict schema enforcement.

$response = $handler->createRequest(
    new AIChatMessage(
        AIChatMessageRoleEnum::USER,
        'List 3 fruits with their color and taste. Return the result as JSON.',
    ),
)
    ->asJson()
    ->addCriteria(CapabilityCriteria::INTERMEDIATE)
    ->execute();

$content = \json_decode($response->getMessage()->content, true, 512, \JSON_THROW_ON_ERROR);

echo "Response:\n";
echo \json_encode($content, \JSON_PRETTY_PRINT | \JSON_THROW_ON_ERROR);
