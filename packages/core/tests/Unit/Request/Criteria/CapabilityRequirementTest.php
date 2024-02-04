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
use ModelflowAi\Core\Request\Criteria\CapabilityRequirement;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class CapabilityRequirementTest extends TestCase
{
    use ProphecyTrait;

    public function testMatches(): void
    {
        $capabilityRequirement = CapabilityRequirement::SMART;

        $this->assertTrue($capabilityRequirement->matches(CapabilityRequirement::BASIC));
    }

    public function testMatchesReturnsFalseWhenCriteriaDoesNotMatch(): void
    {
        $capabilityRequirement = CapabilityRequirement::BASIC;

        $this->assertFalse($capabilityRequirement->matches(CapabilityRequirement::SMART));
    }

    public function testMatchesReturnsTrueForADifferentCriteria(): void
    {
        $mockCriteria = $this->prophesize(AiCriteriaInterface::class);

        $capabilityRequirement = CapabilityRequirement::SMART;

        $this->assertTrue($capabilityRequirement->matches($mockCriteria->reveal()));
    }
}
