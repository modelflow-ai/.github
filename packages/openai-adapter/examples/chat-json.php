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

// OpenAI supports plain JSON mode (json_object) which outputs valid JSON
// but without strict schema enforcement. The model will produce JSON
// but may include additional fields or omit optional ones.

$response = $handler->createRequest(
    new AIChatMessage(
        AIChatMessageRoleEnum::USER,
        'List 3 programming languages with their year of creation and main paradigm. '
            . 'Return the result as a JSON array.',
    ),
)
    ->asJson()
    ->addCriteria(CapabilityCriteria::BASIC)
    ->execute();

$content = \json_decode($response->getMessage()->content, true, 512, \JSON_THROW_ON_ERROR);

echo "Response:\n";
echo \json_encode($content, \JSON_PRETTY_PRINT | \JSON_THROW_ON_ERROR);
