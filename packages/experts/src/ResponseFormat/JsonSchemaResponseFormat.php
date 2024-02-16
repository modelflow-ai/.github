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

class JsonSchemaResponseFormat implements ResponseFormatInterface
{
    /**
     * @param array{
     *     properties?: array<string, array{type: string, description: string}>,
     *     required?: array<string>,
     * } $schema
     */
    public function __construct(
        public array $schema,
    ) {
    }

    public function format(): string
    {
        $lines = [
            'Produce a JSON object that includes:',
        ];

        // Process the properties if they exist
        if (!empty($this->schema['properties'])) {
            $lines[] = 'Properties:';
            foreach ($this->schema['properties'] as $property => $details) {
                $propertyDescription = "- $property";
                if (!empty($details['type'])) {
                    $propertyDescription .= ' (Type: ' . $details['type'] . ')';
                }
                if (!empty($details['description'])) {
                    $propertyDescription .= ': ' . $details['description'];
                }
                $lines[] = $propertyDescription;
            }
        }

        if (!empty($this->schema['required'])) {
            $lines[] = 'Required properties: ' . \implode(', ', \array_values($this->schema['required']));
        }

        $lines[] = 'It\'s crucial that your output is a clean JSON object, presented without any additional formatting, annotations, or explanatory content. The response should be ready to use as-is for a system to store it in the database or to process it further.';

        return \implode("\n", $lines);
    }
}
