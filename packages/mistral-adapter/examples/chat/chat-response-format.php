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

$response = $handler->createRequest(
    new AIChatMessage(AIChatMessageRoleEnum::USER, 'You are a BOT that help me to generate ideas for my project.'),
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
                'description' => 'Should contains 5 projects',
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

/**
 * @param array<string, string|mixed> $data
 */
function formatOutput(array $data, int $indent = 0): string
{
    $output = '';
    foreach ($data as $key => $value) {
        $output .= \str_repeat('  ', $indent);
        if (\is_array($value)) {
            $output .= $key . ":\n";
            /** @var array<string, mixed> $value */
            $output .= formatOutput($value, $indent + 1);
        } else {
            $output .= $key . ': ' . $value . "\n";
        }
    }

    return $output;
}

$content = \json_decode($response->getMessage()->content, true, 512, \JSON_THROW_ON_ERROR);
try {
    /** @var array<string, mixed>|null $content */
    if (null === $content) {
        throw new RuntimeException('Failed to decode JSON response');
    }
    if (!\is_array($content)) {
        throw new RuntimeException('Response is not an array');
    }
    if ([] === $content) {
        throw new RuntimeException('Response is empty');
    }

    echo formatOutput($content);
} catch (JsonException $e) {
    throw new RuntimeException('Failed to decode response: ' . $e->getMessage(), 0, $e);
}
