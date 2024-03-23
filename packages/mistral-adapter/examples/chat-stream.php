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

namespace App;

use ModelflowAi\Core\AIRequestHandlerInterface;
use ModelflowAi\Core\Request\Criteria\CapabilityCriteria;
use ModelflowAi\Core\Request\Message\AIChatMessage;
use ModelflowAi\Core\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Core\Response\AIChatResponseStream;
use ModelflowAi\PromptTemplate\ChatPromptTemplate;

/** @var AIRequestHandlerInterface $handler */
$handler = require_once __DIR__ . '/bootstrap.php';

/** @var AIChatResponseStream $response */
$response = $handler->createChatRequest(
    ...ChatPromptTemplate::create(
        new AIChatMessage(AIChatMessageRoleEnum::SYSTEM, 'You are an {feeling} bot'),
        new AIChatMessage(AIChatMessageRoleEnum::USER, 'Hello {where}!'),
    )->format(['where' => 'world', 'feeling' => 'angry']),
)
    ->addCriteria(CapabilityCriteria::BASIC)
    ->streamed()
    ->build()
    ->execute();

foreach ($response->getMessageStream() as $index => $message) {
    if (0 === $index) {
        echo $message->role->value . ': ';
    }

    echo $message->content;
}
