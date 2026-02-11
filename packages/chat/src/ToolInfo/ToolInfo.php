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
 * Inspired by https://github.com/theodo-group/LLPhant/blob/4825d36/src/Chat/FunctionInfo/FunctionInfo.php.
 *
 * @phpstan-import-type ParameterArray from Parameter
 */
final readonly class ToolInfo
{
    /**
     * @param Parameter[] $parameters
     * @param Parameter[] $requiredParameters
     */
    public function __construct(
        public ToolTypeEnum $type,
        public string $name,
        public string $description,
        public array $parameters,
        public array $requiredParameters = [],
    ) {
    }

    /**
     * @param array<string, mixed> $definition
     */
    public static function fromJsonSchema(array $definition): self
    {
        // Support both OpenAI format (with 'function' wrapper) and direct format
        /** @var mixed $function */
        $function = $definition['function'] ?? $definition;

        if (!\is_array($function)) {
            throw new \InvalidArgumentException('Tool definition must contain a function definition');
        }

        /** @var mixed $name */
        $name = $function['name'] ?? null;
        /** @var mixed $description */
        $description = $function['description'] ?? '';

        if (!\is_string($name)) {
            throw new \InvalidArgumentException('Tool function must have a name');
        }

        if (!\is_string($description)) {
            $description = '';
        }

        /** @var mixed $paramsSchema */
        $paramsSchema = $function['parameters'] ?? [];
        $parameters = [];
        $requiredParams = [];
        /** @var array<string> $requiredNames */
        $requiredNames = [];

        if (\is_array($paramsSchema)) {
            /** @var mixed $required */
            $required = $paramsSchema['required'] ?? [];
            $requiredNames = \is_array($required) ? $required : [];

            /** @var mixed $properties */
            $properties = $paramsSchema['properties'] ?? null;
            if (\is_array($properties)) {
                /** @var mixed $paramSchema */
                foreach ($properties as $paramName => $paramSchema) {
                    if (!\is_string($paramName) || !\is_array($paramSchema)) {
                        continue;
                    }

                    $parameter = Parameter::fromJsonSchema($paramName, $paramSchema);
                    $parameters[] = $parameter;

                    if (\in_array($paramName, $requiredNames, true)) {
                        $requiredParams[] = $parameter;
                    }
                }
            }
        }

        return new self(
            type: ToolTypeEnum::FUNCTION,
            name: $name,
            description: $description,
            parameters: $parameters,
            requiredParameters: $requiredParams,
        );
    }

    /**
     * @return array{
     *     type: string,
     *     name: string,
     *     description: string,
     *     parameters: ParameterArray[],
     *     requiredParameters: ParameterArray[],
     * }
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'name' => $this->name,
            'description' => $this->description,
            'parameters' => \array_map(
                static fn (Parameter $parameter) => $parameter->toArray(),
                $this->parameters,
            ),
            'requiredParameters' => \array_map(
                static fn (Parameter $parameter) => $parameter->toArray(),
                $this->requiredParameters,
            ),
        ];
    }
}
