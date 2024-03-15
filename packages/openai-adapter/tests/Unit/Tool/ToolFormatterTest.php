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

use ModelflowAi\Core\ToolInfo\ToolInfoBuilder;
use ModelflowAi\OpenaiAdapter\Tool\ToolFormatter;
use PHPUnit\Framework\TestCase;

class ToolFormatterTest extends TestCase
{
    public function testFormatTool(): void
    {
        $tool = ToolInfoBuilder::buildToolInfo($this, 'toolMethod1', 'test');

        $this->assertSame([
            'name' => 'test',
            'description' => 'This is a description.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'required' => [
                        'type' => 'string',
                        'description' => 'this is a required parameter',
                    ],
                    'optional' => [
                        'type' => 'string',
                        'description' => 'this is an optional parameter',
                    ],
                ],
                'required' => [
                    'required',
                ],
            ],
        ], ToolFormatter::formatTool($tool));
    }

    public function testFormatTools(): void
    {
        $tool1 = ToolInfoBuilder::buildToolInfo($this, 'toolMethod1', 'test');
        $tool2 = ToolInfoBuilder::buildToolInfo($this, 'toolMethod2', 'test');

        $this->assertSame(
            [
                [
                    'type' => 'function',
                    'function' => [
                        'name' => 'test',
                        'description' => 'This is a description.',
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [
                                'required' => [
                                    'type' => 'string',
                                    'description' => 'this is a required parameter',
                                ],
                                'optional' => [
                                    'type' => 'string',
                                    'description' => 'this is an optional parameter',
                                ],
                            ],
                            'required' => [
                                'required',
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'function',
                    'function' => [
                        'name' => 'test',
                        'description' => '',
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [
                                'test' => [
                                    'type' => 'string',
                                    'description' => '',
                                ],
                            ],
                            'required' => [
                                'test',
                            ],
                        ],
                    ],
                ],
            ],
            ToolFormatter::formatTools([
                $tool1,
                $tool2,
            ]),
        );
    }

    /**
     * This is a description.
     *
     * @param string $required this is a required parameter
     * @param string $optional this is an optional parameter
     */
    public function toolMethod1(string $required, string $optional = ''): string
    {
        return $required . $optional;
    }

    public function toolMethod2(string $test): void
    {
    }
}
