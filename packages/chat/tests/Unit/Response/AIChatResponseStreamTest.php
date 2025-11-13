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

use ModelflowAi\Chat\Request\AIChatStreamedRequest;
use ModelflowAi\Chat\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Chat\Response\AIChatResponseMessage;
use ModelflowAi\Chat\Response\AIChatResponseStream;
use ModelflowAi\Chat\Response\StreamingUsageTracker;
use ModelflowAi\Chat\Response\Usage;
use ModelflowAi\Chat\Response\UsageCallbackInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class AIChatResponseStreamTest extends TestCase
{
    use ProphecyTrait;

    public function testGetMessage(): void
    {
        $request = $this->prophesize(AIChatStreamedRequest::class);

        $response = new AIChatResponseStream($request->reveal(), new \ArrayIterator([
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Lorem'),
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Ipsum'),
        ]));

        $contents = ['Lorem', 'Ipsum'];
        foreach ($response->getMessageStream() as $key => $message) {
            $this->assertSame($contents[$key], $message->content);
        }

        $this->assertSame(AIChatMessageRoleEnum::ASSISTANT, $response->getMessage()->role);
        $this->assertSame('LoremIpsum', $response->getMessage()->content);
    }

    public function testGetUsage(): void
    {
        $request = $this->prophesize(AIChatStreamedRequest::class);

        $response = new AIChatResponseStream($request->reveal(), new \ArrayIterator([
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Lorem'),
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Ipsum'),
        ]));

        $this->assertNull($response->getUsage());
    }

    public function testGetMetadata(): void
    {
        $request = $this->prophesize(AIChatStreamedRequest::class);

        $response = new AIChatResponseStream($request->reveal(), new \ArrayIterator([
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Lorem'),
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Ipsum'),
        ]));

        $this->assertSame([], $response->getMetadata());
    }

    public function testGetRequest(): void
    {
        $request = $this->prophesize(AIChatStreamedRequest::class);

        $response = new AIChatResponseStream($request->reveal(), new \ArrayIterator([
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Lorem'),
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Ipsum'),
        ]));

        $this->assertSame($request->reveal(), $response->getRequest());
    }

    public function testRegisterUsageCallback(): void
    {
        $request = $this->prophesize(AIChatStreamedRequest::class);

        $callback = new class implements UsageCallbackInterface {
            /** @var array<int, array{inputTokens: int, outputTokens: int, totalTokens: int, isFinal: bool}> */
            public array $updates = [];

            public function onUsageUpdate(Usage $usage, bool $isFinal): void
            {
                $this->updates[] = [
                    'inputTokens' => $usage->inputTokens,
                    'outputTokens' => $usage->outputTokens,
                    'totalTokens' => $usage->totalTokens,
                    'isFinal' => $isFinal,
                ];
            }
        };

        $tracker = new StreamingUsageTracker();
        $response = new AIChatResponseStream(
            $request->reveal(),
            new \ArrayIterator([
                new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Lorem'),
            ]),
            [],
            $tracker,
        );

        $response->registerUsageCallback($callback);

        // Simulate usage update
        $usage = new Usage(10, 20, 30);
        $tracker->updateUsage($usage, true);

        $this->assertCount(1, $callback->updates);
        $this->assertSame(10, $callback->updates[0]['inputTokens']);
        $this->assertSame(20, $callback->updates[0]['outputTokens']);
        $this->assertSame(30, $callback->updates[0]['totalTokens']);
        $this->assertTrue($callback->updates[0]['isFinal']);
    }

    public function testGetUsageWithTracker(): void
    {
        $request = $this->prophesize(AIChatStreamedRequest::class);
        $tracker = new StreamingUsageTracker();

        $response = new AIChatResponseStream(
            $request->reveal(),
            new \ArrayIterator([
                new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Lorem'),
            ]),
            [],
            $tracker,
        );

        $this->assertNull($response->getUsage());

        // Update usage through tracker
        $usage = new Usage(10, 20, 30);
        $tracker->updateUsage($usage, true);

        $result = $response->getUsage();
        // @phpstan-ignore-next-line - PHPStan doesn't understand state mutation through tracker
        $this->assertNotNull($result);
        $this->assertSame(10, $result->inputTokens);
        $this->assertSame(20, $result->outputTokens);
        $this->assertSame(30, $result->totalTokens);
    }

    public function testRegisterMultipleCallbacks(): void
    {
        $request = $this->prophesize(AIChatStreamedRequest::class);

        $callback1 = new class implements UsageCallbackInterface {
            public int $count = 0;

            public function onUsageUpdate(Usage $usage, bool $isFinal): void
            {
                ++$this->count;
            }
        };

        $callback2 = new class implements UsageCallbackInterface {
            public int $count = 0;

            public function onUsageUpdate(Usage $usage, bool $isFinal): void
            {
                ++$this->count;
            }
        };

        $tracker = new StreamingUsageTracker();
        $response = new AIChatResponseStream(
            $request->reveal(),
            new \ArrayIterator([
                new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Lorem'),
            ]),
            [],
            $tracker,
        );

        $response->registerUsageCallback($callback1);
        $response->registerUsageCallback($callback2);

        $usage = new Usage(10, 20, 30);
        $tracker->updateUsage($usage, true);

        $this->assertSame(1, $callback1->count);
        $this->assertSame(1, $callback2->count);
    }
}
