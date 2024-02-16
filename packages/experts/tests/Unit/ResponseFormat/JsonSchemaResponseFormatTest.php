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

namespace ModelflowAi\Experts\ResponseFormat;

use PHPUnit\Framework\TestCase;

class JsonSchemaResponseFormatTest extends TestCase
{
    public function testFormat(): void
    {
        $responseFormat = new JsonSchemaResponseFormat([
            'properties' => [
                'name' => [
                    'type' => 'string',
                    'description' => 'The name of the user',
                ],
                'age' => [
                    'type' => 'integer',
                    'description' => 'The age of the user',
                ],
            ],
            'required' => ['name'],
        ]);

        $this->assertSame(<<<Format
Produce a JSON object that includes:
Properties:
- name (Type: string): The name of the user
- age (Type: integer): The age of the user
Required properties: name
It's crucial that your output is a clean JSON object, presented without any additional formatting, annotations, or explanatory content. The response should be ready to use as-is for a system to store it in the database or to process it further.
Format, $responseFormat->format());
    }
}
