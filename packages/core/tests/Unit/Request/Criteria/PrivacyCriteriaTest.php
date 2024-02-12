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
use ModelflowAi\Core\Request\Criteria\PrivacyCriteria;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class PrivacyCriteriaTest extends TestCase
{
    use ProphecyTrait;

    public function testMatches(): void
    {
        $privacyRequirement = PrivacyCriteria::HIGH;

        $this->assertTrue($privacyRequirement->matches(PrivacyCriteria::LOW));
    }

    public function testMatchesReturnsFalseWhenCriteriaDoesNotMatch(): void
    {
        $privacyRequirement = PrivacyCriteria::LOW;

        $this->assertFalse($privacyRequirement->matches(PrivacyCriteria::HIGH));
    }

    public function testMatchesReturnsTrueForADifferentCriteria(): void
    {
        $mockCriteria = $this->prophesize(AiCriteriaInterface::class);

        $privacyRequirement = PrivacyCriteria::HIGH;

        $this->assertTrue($privacyRequirement->matches($mockCriteria->reveal()));
    }
}
