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
use ModelflowAi\Core\Request\Criteria\PrivacyRequirement;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class PrivacyRequirementTest extends TestCase
{
    use ProphecyTrait;

    public function testMatches(): void
    {
        $privacyRequirement = PrivacyRequirement::HIGH;

        $this->assertTrue($privacyRequirement->matches(PrivacyRequirement::LOW));
    }

    public function testMatchesReturnsFalseWhenCriteriaDoesNotMatch(): void
    {
        $privacyRequirement = PrivacyRequirement::LOW;

        $this->assertFalse($privacyRequirement->matches(PrivacyRequirement::HIGH));
    }

    public function testMatchesReturnsTrueForADifferentCriteria(): void
    {
        $mockCriteria = $this->prophesize(AiCriteriaInterface::class);

        $privacyRequirement = PrivacyRequirement::HIGH;

        $this->assertTrue($privacyRequirement->matches($mockCriteria->reveal()));
    }
}
