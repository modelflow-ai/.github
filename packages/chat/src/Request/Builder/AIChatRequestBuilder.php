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

namespace ModelflowAi\Chat\Request\Builder;

use ModelflowAi\Chat\Request\AIChatMessageCollection;
use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\Chat\Request\AIChatStreamedRequest;
use ModelflowAi\Chat\Request\Message\AIChatMessage;
use ModelflowAi\Chat\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Chat\Request\Message\MessagePart;
use ModelflowAi\Chat\Request\ResponseFormat\JsonResponseFormat;
use ModelflowAi\Chat\Request\ResponseFormat\JsonSchemaResponseFormat;
use ModelflowAi\Chat\Request\ResponseFormat\ResponseFormatInterface;
use ModelflowAi\Chat\Response\AIChatResponse;
use ModelflowAi\Chat\ToolInfo\ToolChoiceEnum;
use ModelflowAi\Chat\ToolInfo\ToolInfo;
use ModelflowAi\Chat\ToolInfo\ToolInfoBuilder;
use ModelflowAi\DecisionTree\Criteria\CriteriaCollection;
use ModelflowAi\DecisionTree\Criteria\CriteriaInterface;

/**
 * @phpstan-import-type Schema from JsonSchemaResponseFormat
 */
class AIChatRequestBuilder
{
    public static function create(callable $requestHandler): static
    {
        return new static($requestHandler);
    }

    protected CriteriaCollection $criteria;

    /**
     * @var array{
     *     streamed?: bool,
     *     responseFormat?: ResponseFormatInterface,
     *     toolChoice?: ToolChoiceEnum,
     *     seed?: int,
     *     temperature?: float,
     * }
     */
    protected array $options = [];

    /**
     * @var callable
     */
    protected $requestHandler;

    /**
     * @var AIChatMessage[]
     */
    protected array $messages = [];

    /**
     * @var array<string, array{0: object, 1: string}>
     */
    protected array $tools = [];

    /**
     * @var array<string, mixed>
     */
    protected array $metadata = [];

    final public function __construct(
        callable $requestHandler,
    ) {
        $this->requestHandler = $requestHandler;

        $this->criteria = new CriteriaCollection();
    }

    /**
     * @param array{
     *     seed?: int,
     *     temperature?: float,
     *  } $options
     */
    public function addOptions(array $options): static
    {
        $this->options = \array_merge($this->options, $options);

        return $this;
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function addMetadata(array $metadata): static
    {
        $this->metadata = \array_merge($this->metadata, $metadata);

        return $this;
    }

    /**
     * @param Schema|null $jsonSchema
     */
    public function asJson(?array $jsonSchema = null): static
    {
        if (null !== $jsonSchema) {
            $this->options['responseFormat'] = new JsonSchemaResponseFormat($jsonSchema);

            return $this;
        }

        $this->options['responseFormat'] = new JsonResponseFormat();

        return $this;
    }

    /**
     * @deprecated use AIChatRequestBuilder::createStreamedRequest instead
     */
    public function streamed(): static
    {
        trigger_deprecation('modelflow-ai/chat', '0.3.0', 'Use AIChatRequestBuilder::createStreamedRequest instead.');

        $this->options['streamed'] = true;

        return $this;
    }

    /**
     * @param CriteriaInterface|CriteriaInterface[] $criteria
     */
    public function addCriteria(CriteriaInterface|array $criteria): static
    {
        $criteria = \is_array($criteria) ? $criteria : [$criteria];

        $this->criteria = new CriteriaCollection(
            \array_merge($this->criteria->all, $criteria),
        );

        return $this;
    }

    public function addMessage(AIChatMessage $message): static
    {
        $this->messages[] = $message;

        return $this;
    }

    /**
     * @param AIChatMessage[] $messages
     */
    public function addMessages(array $messages): static
    {
        foreach ($messages as $message) {
            $this->addMessage($message);
        }

        return $this;
    }

    /**
     * @param MessagePart[]|MessagePart|string $content
     */
    public function addSystemMessage(array|MessagePart|string $content): static
    {
        $this->messages[] = new AIChatMessage(AIChatMessageRoleEnum::SYSTEM, $content);

        return $this;
    }

    /**
     * @param MessagePart[]|MessagePart|string $content
     */
    public function addAssistantMessage(array|MessagePart|string $content): static
    {
        $this->messages[] = new AIChatMessage(AIChatMessageRoleEnum::ASSISTANT, $content);

        return $this;
    }

    /**
     * @param MessagePart[]|MessagePart|string $content
     */
    public function addUserMessage(array|MessagePart|string $content): static
    {
        $this->messages[] = new AIChatMessage(AIChatMessageRoleEnum::USER, $content);

        return $this;
    }

    public function toolChoice(ToolChoiceEnum $toolChoice): static
    {
        $this->options['toolChoice'] = $toolChoice;

        return $this;
    }

    public function tool(string $name, object $instance, ?string $method = null): static
    {
        $this->tools[$name] = [$instance, $method ?? $name];

        return $this;
    }

    /**
     * @return ToolInfo[]
     */
    protected function buildToolInfos(): array
    {
        return \array_map(
            fn (string $name, array $tool) => ToolInfoBuilder::buildToolInfo($tool[0], $tool[1], $name),
            \array_keys($this->tools),
            $this->tools,
        );
    }

    /**
     * @deprecated use AIChatRequestBuilder::execute instead
     */
    public function build(): AIChatRequest
    {
        trigger_deprecation('modelflow-ai/chat', '0.3.0', 'Use AIChatRequestBuilder::execute instead.');

        return $this->doBuild();
    }

    private function doBuild(): AIChatRequest
    {
        $toolChoice = $this->options['toolChoice'] ?? ToolChoiceEnum::AUTO;
        $responseFormat = $this->options['responseFormat'] ?? null;
        $streamed = $this->options['streamed'] ?? false;

        $options = $this->options;
        unset($options['toolChoice'], $options['responseFormat'], $options['streamed']);

        if ($streamed) {
            return new AIChatStreamedRequest(
                new AIChatMessageCollection(...$this->messages),
                $this->criteria,
                $this->tools,
                $this->buildToolInfos(),
                $options,
                $this->requestHandler,
                $this->metadata,
                $responseFormat,
                $toolChoice,
            );
        }

        return new AIChatRequest(
            new AIChatMessageCollection(...$this->messages),
            $this->criteria,
            $this->tools,
            $this->buildToolInfos(),
            $options,
            $this->requestHandler,
            $this->metadata,
            $responseFormat,
            $toolChoice,
        );
    }

    public function execute(): AIChatResponse
    {
        return $this->doBuild()->execute();
    }
}
