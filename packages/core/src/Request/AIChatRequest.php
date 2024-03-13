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

namespace ModelflowAi\Core\Request;

use ModelflowAi\Core\Request\Criteria\AIRequestCriteriaCollection;
use ModelflowAi\Core\Request\Criteria\FeatureCriteria;
use ModelflowAi\Core\Request\Message\ImageBase64Part;
use ModelflowAi\Core\Response\AIChatResponse;
use ModelflowAi\Core\ToolInfo\ToolChoiceEnum;
use ModelflowAi\Core\ToolInfo\ToolInfo;

class AIChatRequest extends AIRequest implements AIRequestInterface
{
    /**
     * @param array<string, array{0: object, 1: string}> $tools
     * @param ToolInfo[] $toolInfos
     */
    public function __construct(
        private readonly AIChatMessageCollection $messages,
        AIRequestCriteriaCollection $criteria,
        private readonly array $tools,
        private readonly array $toolInfos,
        array $options,
        callable $requestHandler,
    ) {
        $features = [];

        $latest = $this->messages->latest();
        foreach ($latest?->parts ?? [] as $part) {
            if ($part instanceof ImageBase64Part) {
                $features[] = FeatureCriteria::IMAGE_TO_TEXT;
            }
        }

        if ($this->getOption('streamed', false)) {
            $features[] = FeatureCriteria::STREAM;
        }

        if ([] !== $this->tools && ToolChoiceEnum::AUTO === $this->getOption('toolChoice', ToolChoiceEnum::AUTO)) {
            // FIXME does not work currently when more than one criteria of feature is there
            // $features[] = FeatureCriteria::TOOLS;
        }

        parent::__construct($criteria->withFeatures($features), $options, $requestHandler);
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

    public function execute(): AIChatResponse
    {
        return \call_user_func($this->requestHandler, $this);
    }
}
