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

namespace ModelflowAi\Chat\Request;

use ModelflowAi\Chat\Request\Message\AIChatMessage;
use ModelflowAi\Chat\Request\Message\ImageBase64Part;
use ModelflowAi\Chat\Request\ResponseFormat\ResponseFormatInterface;
use ModelflowAi\Chat\Response\AIChatResponseInterface;
use ModelflowAi\Chat\ToolInfo\ToolChoiceEnum;
use ModelflowAi\Chat\ToolInfo\ToolInfo;
use ModelflowAi\DecisionTree\Behaviour\CriteriaBehaviour;
use ModelflowAi\DecisionTree\Criteria\CriteriaCollection;
use ModelflowAi\DecisionTree\Criteria\FeatureCriteria;
use Webmozart\Assert\Assert;

class AIChatRequest implements CriteriaBehaviour
{
    final public const OPTIONS_KEYS = ['seed', 'temperature'];

    private readonly CriteriaCollection $criteria;

    /**
     * @var callable
     */
    protected $requestHandler;

    /**
     * @param array<string, array{0: object, 1: string}> $tools
     * @param ToolInfo[] $toolInfos
     * @param array{
     *     seed?: int,
     *     temperature?: float,
     * } $options
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private AIChatMessageCollection $messages,
        CriteriaCollection $criteria,
        private readonly array $tools,
        private readonly array $toolInfos,
        private array $options,
        callable $requestHandler,
        private array $metadata = [],
        private readonly ?ResponseFormatInterface $responseFormat = null,
        private readonly ToolChoiceEnum $toolChoice = ToolChoiceEnum::AUTO,
    ) {
        foreach (\array_keys($options) as $key) {
            Assert::oneOf($key, self::OPTIONS_KEYS);
        }

        $features = [];

        $latest = $this->messages->latest();
        foreach ($latest?->parts ?? [] as $part) {
            if ($part instanceof ImageBase64Part) {
                $features[] = FeatureCriteria::IMAGE_TO_TEXT;
            }
        }

        if ([] !== $this->tools && ToolChoiceEnum::AUTO === $this->getToolChoice()) {
            $features[] = FeatureCriteria::TOOLS;
        }

        $this->criteria = $criteria->withFeatures($features);
        $this->requestHandler = $requestHandler;
    }

    /**
     * @param "seed"|"temperature" $key
     */
    public function getOption(string $key, mixed $default = null): mixed
    {
        Assert::oneOf($key, self::OPTIONS_KEYS);

        return $this->options[$key] ?? $default;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getCriteria(): CriteriaCollection
    {
        return $this->criteria;
    }

    /**
     * @return array{
     *     seed?: int,
     *     temperature?: float,
     * }
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function matches(array $criteria): bool
    {
        return $this->criteria->matches($criteria);
    }

    public function getMessages(): AIChatMessageCollection
    {
        return $this->messages;
    }

    public function hasTools(): bool
    {
        return [] !== $this->tools;
    }

    /**
     * @return array<string, array{0: object, 1: string}>
     */
    public function getTools(): array
    {
        return $this->tools;
    }

    /**
     * @return ToolInfo[]
     */
    public function getToolInfos(): array
    {
        return $this->toolInfos;
    }

    public function getToolChoice(): ToolChoiceEnum
    {
        return $this->toolChoice;
    }

    public function getFormat(): ?string
    {
        return $this->responseFormat?->getType();
    }

    public function getResponseFormat(): ?ResponseFormatInterface
    {
        return $this->responseFormat;
    }

    public function isStreamed(): bool
    {
        return false;
    }

    public function execute(): AIChatResponseInterface
    {
        return \call_user_func($this->requestHandler, $this);
    }

    public function withMessage(AIChatMessage $message): static
    {
        $clone = clone $this;
        $clone->messages = new AIChatMessageCollection(...[...$this->messages, $message]);

        return $clone;
    }

    public function withMetadata(array $metadata): static
    {
        $clone = clone $this;
        $clone->metadata = $metadata;

        return $clone;
    }
}
