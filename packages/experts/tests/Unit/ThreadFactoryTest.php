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

namespace ModelflowAi\Experts;

use ModelflowAi\Core\AIRequestHandlerInterface;
use ModelflowAi\Core\Request\Criteria\CapabilityCriteria;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ThreadFactoryTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<AIRequestHandlerInterface>
     */
    private ObjectProphecy $requestHandler;

    protected function setUp(): void
    {
        $this->requestHandler = $this->prophesize(AIRequestHandlerInterface::class);
    }

    public function testCreate(): void
    {
        $expert = new Expert(
            'name',
            'description',
            'instructions',
            [CapabilityCriteria::SMART],
        );

        $threadFactory = new ThreadFactory($this->requestHandler->reveal());
        $thread = $threadFactory->createThread($expert);

        $this->assertInstanceOf(Thread::class, $thread);
    }
}
