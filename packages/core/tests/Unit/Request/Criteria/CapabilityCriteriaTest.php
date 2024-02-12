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
use ModelflowAi\Core\Request\Criteria\CapabilityCriteria;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class CapabilityCriteriaTest extends TestCase
{
    use ProphecyTrait;

    public function testMatches(): void
    {
        $capabilityRequirement = CapabilityCriteria::SMART;

        $this->assertTrue($capabilityRequirement->matches(CapabilityCriteria::BASIC));
    }

    public function testMatchesReturnsFalseWhenCriteriaDoesNotMatch(): void
    {
        $capabilityRequirement = CapabilityCriteria::BASIC;

        $this->assertFalse($capabilityRequirement->matches(CapabilityCriteria::SMART));
    }

    public function testMatchesReturnsTrueForADifferentCriteria(): void
    {
        $mockCriteria = $this->prophesize(AiCriteriaInterface::class);

        $capabilityRequirement = CapabilityCriteria::SMART;

        $this->assertTrue($capabilityRequirement->matches($mockCriteria->reveal()));
    }
}
