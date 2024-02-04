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

namespace ModelflowAi\Core\Tests\Unit\DecisionTree;

use ModelflowAi\Core\DecisionTree\DecisionRule;
use ModelflowAi\Core\Model\AIModelAdapterInterface;
use ModelflowAi\Core\Request\AIRequestInterface;
use ModelflowAi\Core\Request\Criteria\AiCriteriaInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class DecisionRuleTest extends TestCase
{
    use ProphecyTrait;

    public function testMatches(): void
    {
        $adapter = $this->prophesize(AIModelAdapterInterface::class);
        $criteria = $this->prophesize(AiCriteriaInterface::class);
        $request = $this->prophesize(AIRequestInterface::class);
        $request->matches($criteria->reveal())->willReturn(true);
        $adapter->supports($request->reveal())->willReturn(true);

        $decisionRule = new DecisionRule($adapter->reveal(), [$criteria->reveal()]);

        $this->assertTrue($decisionRule->matches($request->reveal()));
    }

    public function testMatchesReturnsFalseWhenCriteriaDoesNotMatch(): void
    {
        $adapter = $this->prophesize(AIModelAdapterInterface::class);
        $criteria = $this->prophesize(AiCriteriaInterface::class);
        $criteria->matches()->willReturn(false);
        $request = $this->prophesize(AIRequestInterface::class);
        $request->matches($criteria->reveal())->willReturn(false);
        $adapter->supports($request->reveal())->willReturn(true);

        $decisionRule = new DecisionRule($adapter->reveal(), [$criteria->reveal()]);

        $this->assertFalse($decisionRule->matches($request->reveal()));
    }

    public function testGetAdapter(): void
    {
        $adapter = $this->prophesize(AIModelAdapterInterface::class);
        $decisionRule = new DecisionRule($adapter->reveal(), []);

        $this->assertSame($adapter->reveal(), $decisionRule->getAdapter());
    }
}
