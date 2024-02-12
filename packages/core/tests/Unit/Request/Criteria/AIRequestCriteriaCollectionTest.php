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

namespace ModelflowAi\Core\Tests\Unit\Request\Criteria;

use ModelflowAi\Core\Request\Criteria\AiCriteriaInterface;
use ModelflowAi\Core\Request\Criteria\AIRequestCriteriaCollection;
use ModelflowAi\Core\Request\Criteria\FeatureCriteria;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class AIRequestCriteriaCollectionTest extends TestCase
{
    use ProphecyTrait;

    public function testMatches(): void
    {
        $toMatch = $this->prophesize(AiCriteriaInterface::class);

        $criteria1 = $this->prophesize(AiCriteriaInterface::class);
        $criteria1->matches($toMatch->reveal())->willReturn(true);
        $criteria2 = $this->prophesize(AiCriteriaInterface::class);
        $criteria2->matches($toMatch->reveal())->willReturn(true);

        $criteriaCollection = new AIRequestCriteriaCollection([$criteria1->reveal(), $criteria2->reveal()]);

        $this->assertTrue($criteriaCollection->matches($toMatch->reveal()));
    }

    public function testMatchesReturnsFalseWhenCriteriaDoesNotMatch(): void
    {
        $toMatch = $this->prophesize(AiCriteriaInterface::class);

        $mockCriteria1 = $this->prophesize(AiCriteriaInterface::class);
        $mockCriteria1->matches($toMatch->reveal())->willReturn(true);
        $mockCriteria2 = $this->prophesize(AiCriteriaInterface::class);
        $mockCriteria2->matches($toMatch->reveal())->willReturn(false);

        $criteriaCollection = new AIRequestCriteriaCollection([$mockCriteria1->reveal(), $mockCriteria2->reveal()]);

        $this->assertFalse($criteriaCollection->matches($toMatch->reveal()));
    }

    public function testWithFeatures(): void
    {
        $criteriaCollection = new AIRequestCriteriaCollection();
        $features = [FeatureCriteria::IMAGE_TO_TEXT];

        $newCriteriaCollection = $criteriaCollection->withFeatures($features);

        $this->assertTrue($newCriteriaCollection->matches(FeatureCriteria::IMAGE_TO_TEXT));
    }
}
