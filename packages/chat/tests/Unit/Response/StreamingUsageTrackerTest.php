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

namespace ModelflowAi\Chat\Tests\Unit\Response;

use ModelflowAi\Chat\Response\StreamingUsageTracker;
use ModelflowAi\Chat\Response\Usage;
use ModelflowAi\Chat\Response\UsageCallbackInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class StreamingUsageTrackerTest extends TestCase
{
    use ProphecyTrait;

    public function testInitialStateWithoutEstimation(): void
    {
        $tracker = new StreamingUsageTracker();

        $this->assertNull($tracker->getUsage());
    }

    public function testInitialStateWithEstimation(): void
    {
        $tracker = new StreamingUsageTracker(true);

        $this->assertNull($tracker->getUsage());
    }

    public function testUpdateUsageWithSingleUpdate(): void
    {
        $tracker = new StreamingUsageTracker();
        $usage = new Usage(10, 20, 30);

        $tracker->updateUsage($usage, false);

        $result = $tracker->getUsage();
        $this->assertNotNull($result);
        $this->assertSame(10, $result->inputTokens);
        $this->assertSame(20, $result->outputTokens);
        $this->assertSame(30, $result->totalTokens);
    }

    public function testUpdateUsageWithMultipleUpdates(): void
    {
        $tracker = new StreamingUsageTracker();

        $usage1 = new Usage(10, 5, 15);
        $usage2 = new Usage(0, 5, 5);
        $usage3 = new Usage(0, 10, 10);

        $tracker->updateUsage($usage1, false);
        $tracker->updateUsage($usage2, false);
        $tracker->updateUsage($usage3, true);

        $result = $tracker->getUsage();
        $this->assertNotNull($result);
        $this->assertSame(10, $result->inputTokens);
        $this->assertSame(20, $result->outputTokens);
        $this->assertSame(30, $result->totalTokens);
    }

    public function testUpdateUsageIgnoresUpdatesAfterFinalization(): void
    {
        $tracker = new StreamingUsageTracker();

        $usage1 = new Usage(10, 20, 30);
        $usage2 = new Usage(5, 10, 15);

        $tracker->updateUsage($usage1, true);
        $tracker->updateUsage($usage2, false);

        $result = $tracker->getUsage();
        $this->assertNotNull($result);
        $this->assertSame(10, $result->inputTokens);
        $this->assertSame(20, $result->outputTokens);
        $this->assertSame(30, $result->totalTokens);
    }

    public function testUpdateUsageWithEstimatedFlag(): void
    {
        $tracker = new StreamingUsageTracker(true);
        $usage = new Usage(10, 20, 30, ['estimated' => true]);

        $tracker->updateUsage($usage, true);

        $result = $tracker->getUsage();
        $this->assertNotNull($result);
        $this->assertTrue($result->isEstimated());
        $this->assertTrue($tracker->isEstimated());
    }

    public function testRegisterCallbackAndReceiveUpdates(): void
    {
        $tracker = new StreamingUsageTracker();
        $receivedUpdates = [];

        $callback = new class($receivedUpdates) implements UsageCallbackInterface {
            public function __construct(private array &$updates)
            {
            }

            public function onUsageUpdate(Usage $usage, bool $isFinal): void
            {
                $this->updates[] = [
                    'usage' => $usage,
                    'isFinal' => $isFinal,
                ];
            }
        };

        $tracker->registerCallback($callback);

        $usage1 = new Usage(10, 5, 15);
        $usage2 = new Usage(0, 10, 10);

        $tracker->updateUsage($usage1, false);
        $tracker->updateUsage($usage2, true);

        $this->assertCount(2, $receivedUpdates);

        $this->assertSame(10, $receivedUpdates[0]['usage']->inputTokens);
        $this->assertFalse($receivedUpdates[0]['isFinal']);

        $this->assertSame(10, $receivedUpdates[1]['usage']->inputTokens);
        $this->assertSame(15, $receivedUpdates[1]['usage']->outputTokens);
        $this->assertTrue($receivedUpdates[1]['isFinal']);
    }

    public function testRegisterMultipleCallbacks(): void
    {
        $tracker = new StreamingUsageTracker();
        $receivedUpdates1 = [];
        $receivedUpdates2 = [];

        $callback1 = new class($receivedUpdates1) implements UsageCallbackInterface {
            public function __construct(private array &$updates)
            {
            }

            public function onUsageUpdate(Usage $usage, bool $isFinal): void
            {
                $this->updates[] = $usage->totalTokens;
            }
        };

        $callback2 = new class($receivedUpdates2) implements UsageCallbackInterface {
            public function __construct(private array &$updates)
            {
            }

            public function onUsageUpdate(Usage $usage, bool $isFinal): void
            {
                $this->updates[] = $usage->totalTokens;
            }
        };

        $tracker->registerCallback($callback1);
        $tracker->registerCallback($callback2);

        $usage = new Usage(10, 20, 30);
        $tracker->updateUsage($usage, true);

        $this->assertSame([30], $receivedUpdates1);
        $this->assertSame([30], $receivedUpdates2);
    }

    public function testCallbacksNotCalledAfterFinalization(): void
    {
        $tracker = new StreamingUsageTracker();
        $callCount = 0;

        $callback = new class($callCount) implements UsageCallbackInterface {
            public function __construct(private int &$count)
            {
            }

            public function onUsageUpdate(Usage $usage, bool $isFinal): void
            {
                ++$this->count;
            }
        };

        $tracker->registerCallback($callback);

        $usage1 = new Usage(10, 20, 30);
        $usage2 = new Usage(5, 10, 15);

        $tracker->updateUsage($usage1, true);
        $tracker->updateUsage($usage2, false);

        $this->assertSame(1, $callCount);
    }
}
