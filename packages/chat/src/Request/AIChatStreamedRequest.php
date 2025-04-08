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

use ModelflowAi\Chat\Request\ResponseFormat\ResponseFormatInterface;
use ModelflowAi\Chat\Response\AIChatResponseStreamInterface;
use ModelflowAi\Chat\ToolInfo\ToolChoiceEnum;
use ModelflowAi\Chat\ToolInfo\ToolInfo;
use ModelflowAi\DecisionTree\Criteria\CriteriaCollection;
use ModelflowAi\DecisionTree\Criteria\FeatureCriteria;

class AIChatStreamedRequest extends AIChatRequest
{
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
        AIChatMessageCollection $messages,
        CriteriaCollection $criteria,
        array $tools,
        array $toolInfos,
        array $options,
        callable $requestHandler,
        array $metadata = [],
        ?ResponseFormatInterface $responseFormat = null,
        ToolChoiceEnum $toolChoice = ToolChoiceEnum::AUTO,
    ) {
        $features = [];
        if ($this->isStreamed()) {
            $features[] = FeatureCriteria::STREAM;
        }

        parent::__construct(
            $messages,
            $criteria->withFeatures($features),
            $tools,
            $toolInfos,
            $options,
            $requestHandler,
            $metadata,
            $responseFormat,
            $toolChoice,
        );
    }

    public function isStreamed(): bool
    {
        return true;
    }

    public function execute(): AIChatResponseStreamInterface
    {
        /** @var AIChatResponseStreamInterface $response */
        $response = \call_user_func($this->requestHandler, $this);

        return $response;
    }
}
