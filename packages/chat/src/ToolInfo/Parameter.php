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

namespace ModelflowAi\Chat\ToolInfo;

/**
 * Inspired by https://github.com/theodo-group/LLPhant/blob/4825d36/src/Chat/FunctionInfo/Parameter.php.
 *
 * @phpstan-type ParameterArray array{
 *     name: string,
 *     type: string,
 *     description: string,
 *     enum: mixed[],
 *     format: string|null,
 *     itemsOrProperties: mixed[]|string|null,
 *     nullable: bool,
 *     required: string[],
 * }
 */
class Parameter
{
    /**
     * @param mixed[] $enum
     * @param Parameter[]|string|null $itemsOrProperties
     * @param string[] $required
     */
    public function __construct(
        public string $name,
        public string $type,
        public string $description,
        public array $enum = [],
        public ?string $format = null,
        public array|string|null $itemsOrProperties = null,
        public bool $nullable = false,
        public array $required = [],
    ) {
    }

    /**
     * @param array<string, mixed> $schema
     */
    public static function fromJsonSchema(string $name, array $schema): self
    {
        /** @var mixed $type */
        $type = $schema['type'] ?? 'string';
        /** @var mixed $description */
        $description = $schema['description'] ?? '';
        $nullable = false;

        // Handle array types like ["string", "null"]
        if (\is_array($type)) {
            [$type, $nullable] = self::parseArrayType($type);
        }

        if (!\is_string($type)) {
            $type = 'string';
        }

        if (!\is_string($description)) {
            $description = '';
        }

        $itemsOrProperties = null;
        $enum = [];
        $format = null;
        $required = self::parseRequiredArray($schema['required'] ?? null);

        /** @var mixed $properties */
        $properties = $schema['properties'] ?? null;
        if ('object' === $type && \is_array($properties)) {
            $nestedProperties = [];
            /** @var mixed $nestedSchema */
            foreach ($properties as $nestedName => $nestedSchema) {
                if (\is_string($nestedName) && \is_array($nestedSchema)) {
                    $nestedProperties[] = self::fromJsonSchema($nestedName, $nestedSchema);
                }
            }
            $itemsOrProperties = $nestedProperties;
        }

        /** @var mixed $items */
        $items = $schema['items'] ?? null;
        if ('array' === $type && null !== $items) {
            if (\is_string($items)) {
                $itemsOrProperties = $items;
            } elseif (\is_array($items)) {
                /** @var mixed $itemsType */
                $itemsType = $items['type'] ?? 'string';
                if (\is_array($itemsType)) {
                    [$itemsType] = self::parseArrayType($itemsType);
                }

                /** @var mixed $itemProperties */
                $itemProperties = $items['properties'] ?? null;
                if ('object' === $itemsType && \is_array($itemProperties)) {
                    $nestedProperties = [];
                    /** @var mixed $nestedSchema */
                    foreach ($itemProperties as $nestedName => $nestedSchema) {
                        if (\is_string($nestedName) && \is_array($nestedSchema)) {
                            $nestedProperties[] = self::fromJsonSchema($nestedName, $nestedSchema);
                        }
                    }
                    $itemsOrProperties = $nestedProperties;
                    $required = self::parseRequiredArray($items['required'] ?? null);
                } else {
                    $itemsOrProperties = \is_string($itemsType) ? $itemsType : 'string';
                }
            }
        }

        /** @var mixed $enumValues */
        $enumValues = $schema['enum'] ?? null;
        if (\is_array($enumValues)) {
            $enum = $enumValues;
        }

        /** @var mixed $formatValue */
        $formatValue = $schema['format'] ?? null;
        if (\is_string($formatValue)) {
            $format = $formatValue;
        }

        return new self(
            name: $name,
            type: $type,
            description: $description,
            enum: $enum,
            format: $format,
            itemsOrProperties: $itemsOrProperties,
            nullable: $nullable,
            required: $required,
        );
    }

    /**
     * @param array<mixed> $types
     *
     * @return array{0: string, 1: bool}
     */
    private static function parseArrayType(array $types): array
    {
        $nullable = \in_array('null', $types, true);
        $primaryType = 'string';

        foreach ($types as $type) {
            if (\is_string($type) && 'null' !== $type) {
                $primaryType = $type;
                break;
            }
        }

        return [$primaryType, $nullable];
    }

    /**
     * @return string[]
     */
    private static function parseRequiredArray(mixed $required): array
    {
        if (!\is_array($required)) {
            return [];
        }

        $result = [];
        foreach ($required as $item) {
            if (\is_string($item)) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * @return ParameterArray
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
            'enum' => $this->enum,
            'format' => $this->format,
            'itemsOrProperties' => $this->serializeItemsOrProperties(),
            'nullable' => $this->nullable,
            'required' => $this->required,
        ];
    }

    /**
     * @return mixed[]|string|null
     */
    private function serializeItemsOrProperties(): array|string|null
    {
        if (null === $this->itemsOrProperties || \is_string($this->itemsOrProperties)) {
            return $this->itemsOrProperties;
        }

        return \array_map(
            static fn (Parameter $parameter) => $parameter->toArray(),
            $this->itemsOrProperties,
        );
    }
}
