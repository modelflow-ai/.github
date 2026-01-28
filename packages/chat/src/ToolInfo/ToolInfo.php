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
