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

namespace ModelflowAi\Chat\Tests\Unit\Util\Json;

use ModelflowAi\Chat\Util\Json\OptimisticJsonParser;
use PHPUnit\Framework\TestCase;

class OptimisticJsonParserTest extends TestCase
{
    /**
     * @dataProvider validJsonProvider
     */
    public function testParseWithValidJson(string $json, mixed $expectedResult): void
    {
        $result = OptimisticJsonParser::parse($json);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @dataProvider incompleteJsonProvider
     */
    public function testParseWithIncompleteJson(string $json, mixed $expectedResult): void
    {
        $result = OptimisticJsonParser::parse($json);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @dataProvider streamedJsonProvider
     */
    public function testParseWithStreamedJson(string $json, mixed $expectedResult): void
    {
        $result = OptimisticJsonParser::parse($json);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @dataProvider invalidJsonProvider
     */
    public function testParseWithInvalidJson(string $json, mixed $expectedResult): void
    {
        $result = OptimisticJsonParser::parse($json);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @dataProvider edgeCasesProvider
     */
    public function testParseWithEdgeCases(string $json, mixed $expectedResult): void
    {
        $result = OptimisticJsonParser::parse($json);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @dataProvider falsyValuesProvider
     */
    public function testParseWithFalsyValues(string $json, mixed $expectedResult): void
    {
        $result = OptimisticJsonParser::parse($json);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array<string, array{0: string, 1: mixed}>
     */
    public static function validJsonProvider(): array
    {
        return [
            'empty object' => ['{}', []],
            'empty array' => ['[]', []],
            'simple object' => ['{"key":"value"}', ['key' => 'value']],
            'simple array' => ['[1,2,3]', [1, 2, 3]],
            'nested object' => ['{"obj":{"key":"value"}}', ['obj' => ['key' => 'value']]],
            'nested array' => ['[1,[2,3],4]', [1, [2, 3], 4]],
            'complex json' => [
                '{"name":"John","age":30,"city":"New York","skills":["PHP","JavaScript"]}',
                ['name' => 'John', 'age' => 30, 'city' => 'New York', 'skills' => ['PHP', 'JavaScript']],
            ],
        ];
    }

    /**
     * @return array<string, array{0: string, 1: mixed}>
     */
    public static function incompleteJsonProvider(): array
    {
        return [
            'unclosed object' => ['{"key":"value"', ['key' => 'value']],
            'unclosed array' => ['[1,2,3', [1, 2, 3]],
            'trailing comma in object' => ['{"key1":"value1", "key2":"value2",}', ['key1' => 'value1', 'key2' => 'value2']],
            'trailing comma in array' => ['[1,2,3,]', [1, 2, 3]],
            'unclosed string' => ['{"key":"value', ['key' => 'value']],
            'unclosed nested structures' => ['{"obj":{"key":"value"', ['obj' => ['key' => 'value']]],
            'multiple unclosed objects' => ['{"a":{"b":{"c":"d"', ['a' => ['b' => ['c' => 'd']]]],
            'unclosed array and object' => ['[{"key":"value"', [['key' => 'value']]],
            'dangling colon' => ['{"key":', ['key' => null]],
            'dangling colon with space' => ['{"key": ', ['key' => null]],
            'dangling colon in nested' => ['{"a": {"b":', ['a' => ['b' => null]]],
            'partial true' => ['{"key": tru', ['key' => true]],
            'partial false' => ['{"key": fal', ['key' => false]],
            'partial false fals' => ['{"key": fals', ['key' => false]],
            'partial null' => ['{"key": nul', ['key' => null]],
            'partial null nu' => ['{"key": nu', ['key' => null]],
            'trailing comma at end of object' => ['{"a": 1,', ['a' => 1]],
            'trailing comma at end of array' => ['[1, 2,', [1, 2]],
        ];
    }

    /**
     * @return array<string, array{0: string, 1: mixed}>
     */
    public static function streamedJsonProvider(): array
    {
        return [
            'openai chat completion partial' => [
                '{"id":"chatcmpl-123","object":"chat.completion.chunk","created":1694268190,"model":"gpt-3.5-turbo-0613",
                "choices":[{"index":0,"delta":{"role":"assistant","content":"Hello"},"finish_reason":null}]',
                [
                    'id' => 'chatcmpl-123',
                    'object' => 'chat.completion.chunk',
                    'created' => 1_694_268_190,
                    'model' => 'gpt-3.5-turbo-0613',
                    'choices' => [
                        [
                            'index' => 0,
                            'delta' => [
                                'role' => 'assistant',
                                'content' => 'Hello',
                            ],
                            'finish_reason' => null,
                        ],
                    ],
                ],
            ],
            'openai chat completion broken mid-content' => [
                '{"id":"chatcmpl-123","object":"chat.completion.chunk","created":1694268190,"model":"gpt-3.5-turbo-0613",
                "choices":[{"index":0,"delta":{"content":"Hello worl',
                [
                    'id' => 'chatcmpl-123',
                    'object' => 'chat.completion.chunk',
                    'created' => 1_694_268_190,
                    'model' => 'gpt-3.5-turbo-0613',
                    'choices' => [
                        [
                            'index' => 0,
                            'delta' => [
                                'content' => 'Hello worl',
                            ],
                        ],
                    ],
                ],
            ],
            'openai chat completion final chunk partial' => [
                '{"id":"chatcmpl-123","object":"chat.completion.chunk","created":1694268190,"model":"gpt-3.5-turbo-0613",
                "choices":[{"index":0,"delta":{},"finish_reason":"stop',
                [
                    'id' => 'chatcmpl-123',
                    'object' => 'chat.completion.chunk',
                    'created' => 1_694_268_190,
                    'model' => 'gpt-3.5-turbo-0613',
                    'choices' => [
                        [
                            'index' => 0,
                            'delta' => [],
                            'finish_reason' => 'stop',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, array{0: string, 1: mixed}>
     */
    public static function invalidJsonProvider(): array
    {
        return [
            'completely invalid' => ['not json at all', null],
            'malformed json' => ['{"key"::value"}', null],
            'invalid syntax' => ['{"key":"value"}}}}', null],
            'invalid nesting' => ['{"key":["value"}}', null],
        ];
    }

    /**
     * @return array<string, array{0: string, 1: mixed}>
     */
    public static function edgeCasesProvider(): array
    {
        return [
            'empty string' => ['', null],
            'whitespace only' => ['   ', null],
            'single quote' => ["'", null],
            'double quote' => ['"', ''],
            'single opening brace' => ['{', []],
            'single opening bracket' => ['[', []],
        ];
    }

    /**
     * @return array<string, array{0: string, 1: mixed}>
     */
    public static function falsyValuesProvider(): array
    {
        return [
            'zero integer' => ['0', 0],
            'zero float' => ['0.0', 0.0],
            'false' => ['false', false],
            'null' => ['null', null],
            'empty string value' => ['""', ''],
            'string zero' => ['"0"', '0'],
            'object with zero' => ['{"a": 0}', ['a' => 0]],
            'object with false' => ['{"a": false}', ['a' => false]],
            'object with null' => ['{"a": null}', ['a' => null]],
            'object with empty string' => ['{"a": ""}', ['a' => '']],
            'array with falsy values' => ['[0, false, null, ""]', [0, false, null, '']],
        ];
    }

    public function testParseWithEscapedQuotes(): void
    {
        $json = '{"message":"This is a \"quoted\" string"}';
        $expectedResult = ['message' => 'This is a "quoted" string'];

        $result = OptimisticJsonParser::parse($json);
        $this->assertSame($expectedResult, $result);
    }

    public function testProgressiveJsonStream(): void
    {
        $chunks = [
            '{"id":"chat',
            '{"id":"chatcmpl-123","object":"chat.comp',
            '{"id":"chatcmpl-123","object":"chat.completion.chunk","created":1694268190',
            '{"id":"chatcmpl-123","object":"chat.completion.chunk","created":1694268190,"choices":[{"index":0',
            '{"id":"chatcmpl-123","object":"chat.completion.chunk","created":1694268190,"choices":[{"index":0,"delta":{"content":"Hello"}}]}',
        ];

        foreach ($chunks as $index => $chunk) {
            $result = OptimisticJsonParser::parse($chunk);
            $this->assertNotNull($result, "Failed to parse chunk $index: $chunk");

            if ($index === \count($chunks) - 1) {
                $standardParse = \json_decode($chunk, true);
                $this->assertSame($standardParse, $result, 'Last chunk should parse normally');
            }
        }
    }

    public function testErrorMessagePropagation(): void
    {
        $errorMessage = null;

        // Valid JSON should not set error message
        OptimisticJsonParser::parse('{"key": "value"}', $errorMessage);
        $this->assertNull($errorMessage);

        // Fixable JSON sets error message from initial parse attempt, then overwrites on successful fix
        $errorMessage = null;
        $result = OptimisticJsonParser::parse('{"key": "value"', $errorMessage);
        $this->assertSame(['key' => 'value'], $result);

        // Unfixable JSON should set error message
        $errorMessage = null;
        OptimisticJsonParser::parse('not json at all', $errorMessage);
        $this->assertNotNull($errorMessage);
        $this->assertNotEmpty($errorMessage);
    }
}
