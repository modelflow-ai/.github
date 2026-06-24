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

namespace ModelflowAi\Mistral\Responses\Ocr;

use ModelflowAi\ApiClient\Responses\MetaInformation;
use Webmozart\Assert\Assert;

final readonly class ProcessResponse
{
    /**
     * @param Page[] $pages
     */
    private function __construct(
        public array $pages,
        public string $model,
        public UsageInfo $usageInfo,
        public ?string $documentAnnotation,
        public MetaInformation $meta,
    ) {
    }

    /**
     * @param array<mixed> $attributes
     */
    public static function from(array $attributes, MetaInformation $meta): self
    {
        $rawPages = $attributes['pages'];
        $model = $attributes['model'];
        $rawUsageInfo = $attributes['usage_info'];
        $documentAnnotation = $attributes['document_annotation'] ?? null;

        Assert::isArray($rawPages);
        Assert::string($model);
        Assert::isArray($rawUsageInfo);
        Assert::nullOrString($documentAnnotation);

        $pages = \array_map(
            static function (mixed $page): Page {
                Assert::isArray($page);

                return Page::from($page);
            },
            $rawPages,
        );

        return new self(
            $pages,
            $model,
            UsageInfo::from($rawUsageInfo),
            $documentAnnotation,
            $meta,
        );
    }
}
