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

// Google Gemini supports provider-enforced structured output with JSON schema.
// The response is guaranteed to match the schema.

$response = $handler->createRequest(
    new AIChatMessage(
        AIChatMessageRoleEnum::USER,
        'You are a helpful assistant that generates project ideas. '
            . 'Analyze the following topic and suggest 5 related project ideas: "web development"',
    ),
)
    ->asJson([
        'type' => 'object',
        'properties' => [
            'bestIdeaTitle' => [
                'type' => 'string',
                'description' => 'Title of the best idea (should not be empty)',
            ],
            'projects' => [
                'type' => 'array',
                'description' => 'List of 5 project ideas',
                'minItems' => 5,
                'maxItems' => 5,
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'title' => [
                            'type' => 'string',
                            'description' => 'Title of the project (should not be empty)',
                        ],
                        'description' => [
                            'type' => 'string',
                            'description' => 'Description of the project',
                        ],
                    ],
                    'required' => ['title', 'description'],
                ],
            ],
        ],
        'required' => ['bestIdeaTitle', 'projects'],
    ])
    ->addCriteria(CapabilityCriteria::BASIC)
    ->execute();

$content = \json_decode($response->getMessage()->content, true, 512, \JSON_THROW_ON_ERROR);

echo "Response:\n";
echo \json_encode($content, \JSON_PRETTY_PRINT | \JSON_THROW_ON_ERROR);
