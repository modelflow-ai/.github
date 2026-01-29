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

namespace ModelflowAi\DecisionTree\Tests\Unit\Criteria;

use ModelflowAi\DecisionTree\Criteria\CapabilityCriteria;
use ModelflowAi\DecisionTree\Criteria\CriteriaCollection;
use ModelflowAi\DecisionTree\Criteria\CriteriaInterface;
use ModelflowAi\DecisionTree\Criteria\FeatureCriteria;
use ModelflowAi\DecisionTree\Criteria\PrivacyCriteria;
use ModelflowAi\DecisionTree\DecisionEnum;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class CriteriaCollectionTest extends TestCase
{
    use ProphecyTrait;

    public function testMatchesWithMocks(): void
    {
        $toMatch = $this->prophesize(CriteriaInterface::class);

        $criteria1 = $this->prophesize(CriteriaInterface::class);
        $criteria1->matches($toMatch->reveal())->willReturn(DecisionEnum::MATCH);
        $criteria2 = $this->prophesize(CriteriaInterface::class);
        $criteria2->matches($toMatch->reveal())->willReturn(DecisionEnum::MATCH);

        $criteriaCollection = new CriteriaCollection([$criteria1->reveal(), $criteria2->reveal()]);

        $this->assertTrue($criteriaCollection->matches([$toMatch->reveal()]));
    }

    public function testMatchesReturnsFalseWhenCriteriaDoesNotMatch(): void
    {
        $toMatch = $this->prophesize(CriteriaInterface::class);

        $mockCriteria1 = $this->prophesize(CriteriaInterface::class);
        $mockCriteria1->matches($toMatch->reveal())->willReturn(DecisionEnum::MATCH);
        $mockCriteria2 = $this->prophesize(CriteriaInterface::class);
        $mockCriteria2->matches($toMatch->reveal())->willReturn(DecisionEnum::NO_MATCH);

        $criteriaCollection = new CriteriaCollection([$mockCriteria1->reveal(), $mockCriteria2->reveal()]);

        $this->assertFalse($criteriaCollection->matches([$toMatch->reveal()]));
    }

    public function testWithFeatures(): void
    {
        $criteriaCollection = new CriteriaCollection();
        $features = [FeatureCriteria::IMAGE_TO_TEXT];

        $newCriteriaCollection = $criteriaCollection->withFeatures($features);

        $this->assertTrue($newCriteriaCollection->matches([FeatureCriteria::IMAGE_TO_TEXT]));
    }

    /**
     * @return array<array{
     *     0: CriteriaInterface[],
     *     1: CriteriaInterface[],
     *     2: bool,
     * }>
     */
    public static function provideMatches(): array
    {
        return [
            '0' => [
                [FeatureCriteria::IMAGE_TO_TEXT],
                [FeatureCriteria::IMAGE_TO_TEXT, CapabilityCriteria::ADVANCED],
                true,
            ],
            '1' => [
                [FeatureCriteria::IMAGE_TO_TEXT, CapabilityCriteria::SMART],
                [FeatureCriteria::IMAGE_TO_TEXT, CapabilityCriteria::ADVANCED],
                false,
            ],
            '2' => [
                [FeatureCriteria::IMAGE_TO_TEXT, CapabilityCriteria::ADVANCED],
                [FeatureCriteria::IMAGE_TO_TEXT, CapabilityCriteria::ADVANCED],
                true,
            ],
            '3' => [
                [FeatureCriteria::IMAGE_TO_TEXT],
                [FeatureCriteria::STREAM],
                false,
            ],
            '4' => [
                [FeatureCriteria::IMAGE_TO_TEXT],
                [FeatureCriteria::IMAGE_TO_TEXT],
                true,
            ],
            '5' => [
                [FeatureCriteria::IMAGE_TO_TEXT],
                [FeatureCriteria::STREAM],
                false,
            ],
            '6' => [
                [FeatureCriteria::IMAGE_TO_TEXT],
                [FeatureCriteria::IMAGE_TO_TEXT, FeatureCriteria::STREAM, CapabilityCriteria::ADVANCED],
                true,
            ],
            '7' => [
                [FeatureCriteria::IMAGE_TO_TEXT, FeatureCriteria::STREAM],
                [FeatureCriteria::IMAGE_TO_TEXT, FeatureCriteria::STREAM, CapabilityCriteria::ADVANCED],
                true,
            ],
            '8' => [
                [FeatureCriteria::IMAGE_TO_TEXT, CapabilityCriteria::ADVANCED],
                [FeatureCriteria::IMAGE_TO_TEXT, FeatureCriteria::STREAM, CapabilityCriteria::ADVANCED],
                true,
            ],
            '9' => [
                [FeatureCriteria::IMAGE_TO_TEXT, FeatureCriteria::STREAM, CapabilityCriteria::ADVANCED],
                [FeatureCriteria::IMAGE_TO_TEXT, FeatureCriteria::STREAM, CapabilityCriteria::ADVANCED],
                true,
            ],
            '10' => [
                [FeatureCriteria::IMAGE_TO_TEXT, FeatureCriteria::STREAM, FeatureCriteria::TOOLS],
                [FeatureCriteria::IMAGE_TO_TEXT, FeatureCriteria::STREAM, CapabilityCriteria::ADVANCED],
                false,
            ],
            '11' => [
                [CapabilityCriteria::ADVANCED],
                [FeatureCriteria::IMAGE_TO_TEXT, FeatureCriteria::STREAM, CapabilityCriteria::ADVANCED],
                true,
            ],
            '12' => [
                [CapabilityCriteria::BASIC],
                [FeatureCriteria::IMAGE_TO_TEXT, FeatureCriteria::STREAM, CapabilityCriteria::ADVANCED],
                true,
            ],
            '13' => [
                [CapabilityCriteria::SMART],
                [FeatureCriteria::IMAGE_TO_TEXT, FeatureCriteria::STREAM, CapabilityCriteria::ADVANCED],
                false,
            ],
            '14' => [
                [FeatureCriteria::IMAGE_TO_TEXT, FeatureCriteria::STREAM, CapabilityCriteria::ADVANCED],
                [CapabilityCriteria::SMART],
                true,
            ],
            '15' => [
                [CapabilityCriteria::BASIC, PrivacyCriteria::LOW],
                [CapabilityCriteria::BASIC],
                true,
            ],
        ];
    }

    /**
     * @param CriteriaInterface[] $requestCriteria
     * @param CriteriaInterface[] $ruleCriteria
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideMatches')]
    public function testMatchesWithDifferentCombinations(
        array $requestCriteria,
        array $ruleCriteria,
        bool $expected,
    ): void {
        $criteriaCollection = new CriteriaCollection($requestCriteria);

        $this->assertSame($expected, $criteriaCollection->matches($ruleCriteria));
    }
}
