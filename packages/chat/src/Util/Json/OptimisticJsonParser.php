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

namespace ModelflowAi\Chat\Util\Json;

class OptimisticJsonParser
{
    public static function parse(string $jsonString, ?string &$errorMessage = null): mixed
    {
        $data = @\json_decode($jsonString, true);

        if (\JSON_ERROR_NONE === \json_last_error()) {
            return $data;
        }

        $errorMessage = \json_last_error_msg();

        return self::fixAndParseJson($jsonString, $errorMessage);
    }

    private static function fixAndParseJson(string $jsonString, ?string &$errorMessage = null): mixed
    {
        $fixedJsonString = self::fixJsonSyntax($jsonString);

        $data = @\json_decode($fixedJsonString, true);

        if (\JSON_ERROR_NONE === \json_last_error()) {
            return $data;
        }

        $errorMessage = \json_last_error_msg();

        return null;
    }

    private static function fixJsonSyntax(string $jsonString): string
    {
        $jsonString = \preg_replace('/,\s*([\]}])/', '$1', $jsonString);
        if (null === $jsonString) {
            throw new \RuntimeException('Error while fixing JSON syntax');
        }

        $jsonString = self::closeUnclosedStrings($jsonString);
        $jsonString = self::fixIncompleteValues($jsonString);
        $jsonString = self::closeUnclosedStructures($jsonString);

        return $jsonString;
    }

    private static function closeUnclosedStrings(string $jsonString): string
    {
        $inString = false;
        $escaped = false;
        $fixedString = '';

        for ($i = 0; $i < \strlen($jsonString); ++$i) {
            $char = $jsonString[$i];

            if ('"' === $char && !$escaped) {
                $inString = !$inString;
            }

            $escaped = '\\' === $char && !$escaped;

            $fixedString .= $char;
        }

        if ($inString) {
            $fixedString .= '"';
        }

        return $fixedString;
    }

    /**
     * Fix incomplete values such as dangling colons, partial literals, and trailing commas.
     */
    private static function fixIncompleteValues(string $jsonString): string
    {
        $trimmed = \rtrim($jsonString);

        // Dangling colon: {"key": → {"key": null
        if (\preg_match('/:\s*$/', $trimmed)) {
            $jsonString = $trimmed . 'null';
        }

        // Partial boolean/null literals in value position
        $trimmed = \rtrim($jsonString);
        if (\preg_match('/([\s:,\[\{])tru$/i', $trimmed)) {
            $jsonString = \substr($trimmed, 0, -3) . 'true';
        } elseif (\preg_match('/([\s:,\[\{])tr$/i', $trimmed)) {
            $jsonString = \substr($trimmed, 0, -2) . 'true';
        } elseif (\preg_match('/([\s:,\[\{])fals$/i', $trimmed)) {
            $jsonString = \substr($trimmed, 0, -4) . 'false';
        } elseif (\preg_match('/([\s:,\[\{])fal$/i', $trimmed)) {
            $jsonString = \substr($trimmed, 0, -3) . 'false';
        } elseif (\preg_match('/([\s:,\[\{])fa$/i', $trimmed)) {
            $jsonString = \substr($trimmed, 0, -2) . 'false';
        } elseif (\preg_match('/([\s:,\[\{])nul$/i', $trimmed)) {
            $jsonString = \substr($trimmed, 0, -3) . 'null';
        } elseif (\preg_match('/([\s:,\[\{])nu$/i', $trimmed)) {
            $jsonString = \substr($trimmed, 0, -2) . 'null';
        }

        // Trailing comma before end of string (will be followed by structure closure)
        $trimmed = \rtrim($jsonString);
        if (\preg_match('/,\s*$/', $trimmed)) {
            $jsonString = \preg_replace('/,\s*$/', '', $trimmed) ?? $trimmed;
        }

        return $jsonString;
    }

    private static function closeUnclosedStructures(string $jsonString): string
    {
        $stack = [];
        $inString = false;
        $escaped = false;

        for ($i = 0; $i < \strlen($jsonString); ++$i) {
            $char = $jsonString[$i];

            if ('"' === $char && !$escaped) {
                $inString = !$inString;
            }

            $escaped = '\\' === $char && !$escaped;

            if (!$inString) {
                if ('[' === $char || '{' === $char) {
                    $stack[] = $char;
                } elseif (']' === $char) {
                    if ([] !== $stack && '[' === $stack[\count($stack) - 1]) {
                        \array_pop($stack);
                    }
                } elseif ('}' === $char) {
                    if ([] !== $stack && '{' === $stack[\count($stack) - 1]) {
                        \array_pop($stack);
                    }
                }
            }
        }

        $result = $jsonString;
        while ([] !== $stack) {
            $openChar = \array_pop($stack);
            $closeChar = ('[' === $openChar) ? ']' : '}';
            $result .= $closeChar;
        }

        return $result;
    }
}
