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

use ModelflowAi\PromptTemplate\PromptTemplate;

require_once __DIR__ . '/vendor/autoload.php';

$promptTemplate = new PromptTemplate(
    <<<'PROMPT'
Human: What is the capital of {place}?
AI: The capital of {place} is {capital}
PROMPT
);

echo $promptTemplate->format(['place' => 'Germany', 'capital' => 'Berlin']) . "\n";
