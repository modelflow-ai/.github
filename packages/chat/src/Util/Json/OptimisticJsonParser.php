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
    /**
     * Parse a JSON string with best-effort parsing.
     *
     * @param string $jsonString the JSON string to parse
     *
     * @return mixed the parsed JSON data
     */
    public static function parse(string $jsonString, ?string &$errorMessage = null): mixed
    {
        // Attempt to decode the JSON string
        $data = @\json_decode($jsonString, true);

        // Check if decoding was successful
        if (\JSON_ERROR_NONE === \json_last_error()) {
            return $data;
        }

        $errorMessage = \json_last_error_msg();

        // If decoding failed, attempt to fix and parse the JSON
        return self::fixAndParseJson($jsonString);
    }

    /**
     * Attempt to fix and parse an incomplete JSON string.
     *
     * @param string $jsonString the incomplete JSON string
     *
     * @return mixed the parsed JSON data
     */
    private static function fixAndParseJson(string $jsonString, ?string &$errorMessage = null): mixed
    {
        // Attempt to fix common JSON issues
        $fixedJsonString = self::fixJsonSyntax($jsonString);

        // Attempt to decode the fixed JSON string
        $data = @\json_decode($fixedJsonString, true);

        // Check if decoding was successful
        if (\JSON_ERROR_NONE === \json_last_error()) {
            return $data;
        }

        $errorMessage = \json_last_error_msg();

        // If decoding still fails, return null or handle the error
        return null;
    }

    /**
     * Attempt to fix common JSON syntax issues.
     *
     * @param string $jsonString the JSON string with potential syntax issues
     *
     * @return string the fixed JSON string
     */
    private static function fixJsonSyntax(string $jsonString): string
    {
        // Remove trailing commas
        $jsonString = \preg_replace('/,\s*([\]}])/', '$1', $jsonString);
        if (null === $jsonString) {
            throw new \RuntimeException('Error while fixing JSON syntax');
        }

        // Attempt to close unclosed strings
        $jsonString = self::closeUnclosedStrings($jsonString);

        // Attempt to close unclosed objects or arrays
        $jsonString = self::closeUnclosedStructures($jsonString);

        return $jsonString;
    }

    /**
     * Attempt to close unclosed strings in a JSON string.
     *
     * @param string $jsonString the JSON string with potential unclosed strings
     *
     * @return string the JSON string with closed strings
     */
    private static function closeUnclosedStrings(string $jsonString): string
    {
        // Track open quotes
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

        // If string is unclosed, close it
        if ($inString) {
            $fixedString .= '"';
        }

        return $fixedString;
    }

    /**
     * Attempt to close unclosed objects or arrays in a JSON string.
     *
     * @param string $jsonString the JSON string with potential unclosed structures
     *
     * @return string the JSON string with closed structures
     */
    private static function closeUnclosedStructures(string $jsonString): string
    {
        // Track open brackets and track structure properly
        $stack = [];
        $inString = false;
        $escaped = false;

        for ($i = 0; $i < \strlen($jsonString); ++$i) {
            $char = $jsonString[$i];

            // Skip characters in strings (except end quotes)
            if ('"' === $char && !$escaped) {
                $inString = !$inString;
            }

            // Update escape state
            $escaped = '\\' === $char && !$escaped;

            // Only process structural characters when not in a string
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

        // Close any unclosed brackets or braces in the correct order
        $result = $jsonString;
        while ([] !== $stack) {
            $openChar = \array_pop($stack);
            $closeChar = ('[' === $openChar) ? ']' : '}';
            $result .= $closeChar;
        }

        return $result;
    }
}
