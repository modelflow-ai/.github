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

use ModelflowAi\Core\DecisionTree\AIModelDecisionTree;
use ModelflowAi\Core\DecisionTree\DecisionRuleInterface;
use ModelflowAi\Core\Model\AIModelAdapterInterface;
use ModelflowAi\Core\Request\AIRequestInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class AIModelDecisionTreeTest extends TestCase
{
    use ProphecyTrait;

    public function testDetermineAdapter(): void
    {
        $adapter = $this->prophesize(AIModelAdapterInterface::class);
        $rule = $this->prophesize(DecisionRuleInterface::class);
        $rule->getAdapter()->willReturn($adapter->reveal());
        $request = $this->prophesize(AIRequestInterface::class);
        $rule->matches($request->reveal())->willReturn(true);

        $decisionTree = new AIModelDecisionTree([$rule->reveal()]);

        $this->assertSame($adapter->reveal(), $decisionTree->determineAdapter($request->reveal()));
    }

    public function testDetermineAdapterThrowsExceptionWhenNoMatchingRule(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No matching adapter found.');

        $rule = $this->prophesize(DecisionRuleInterface::class);
        $request = $this->prophesize(AIRequestInterface::class);
        $rule->matches($request->reveal())->willReturn(false);

        $decisionTree = new AIModelDecisionTree([$rule->reveal()]);
        $decisionTree->determineAdapter($request->reveal());
    }
}
