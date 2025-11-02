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

use ModelflowAi\Chat\Response\Usage;
use PHPUnit\Framework\TestCase;

class UsageTest extends TestCase
{
    public function testInputTokens(): void
    {
        $usage = new Usage(1, 2, 3);
        $this->assertSame(1, $usage->inputTokens);
    }

    public function testOutputTokens(): void
    {
        $usage = new Usage(1, 2, 3);
        $this->assertSame(2, $usage->outputTokens);
    }

    public function testTotalTokens(): void
    {
        $usage = new Usage(1, 2, 3);
        $this->assertSame(3, $usage->totalTokens);
    }

    public function testMetadata(): void
    {
        $usage = new Usage(1, 2, 3, ['estimated' => true, 'custom' => 'value']);
        $this->assertSame(['estimated' => true, 'custom' => 'value'], $usage->metadata);
    }

    public function testMetadataDefaultsToEmptyArray(): void
    {
        $usage = new Usage(1, 2, 3);
        $this->assertSame([], $usage->metadata);
    }

    public function testIsEstimatedReturnsTrueWhenMetadataSet(): void
    {
        $usage = new Usage(1, 2, 3, ['estimated' => true]);
        $this->assertTrue($usage->isEstimated());
    }

    public function testIsEstimatedReturnsFalseWhenMetadataNotSet(): void
    {
        $usage = new Usage(1, 2, 3);
        $this->assertFalse($usage->isEstimated());
    }

    public function testIsEstimatedReturnsFalseWhenMetadataSetToFalse(): void
    {
        $usage = new Usage(1, 2, 3, ['estimated' => false]);
        $this->assertFalse($usage->isEstimated());
    }

    public function testAddCombinesUsage(): void
    {
        $usage1 = new Usage(10, 20, 30);
        $usage2 = new Usage(5, 10, 15);

        $result = $usage1->add($usage2);

        $this->assertSame(15, $result->inputTokens);
        $this->assertSame(30, $result->outputTokens);
        $this->assertSame(45, $result->totalTokens);
    }

    public function testAddMergesMetadata(): void
    {
        $usage1 = new Usage(10, 20, 30, ['key1' => 'value1', 'estimated' => false]);
        $usage2 = new Usage(5, 10, 15, ['key2' => 'value2', 'estimated' => true]);

        $result = $usage1->add($usage2);

        // array_merge gives precedence to the second array (nextUsage)
        $this->assertSame([
            'key1' => 'value1',
            'estimated' => true, // second usage metadata takes precedence
            'key2' => 'value2',
        ], $result->metadata);
    }

    public function testAddGivesSecondUsageMetadataPrecedence(): void
    {
        $usage1 = new Usage(10, 20, 30, ['estimated' => false]);
        $usage2 = new Usage(5, 10, 15, ['estimated' => true]);

        $result = $usage1->add($usage2);

        // The second usage metadata takes precedence
        $this->assertTrue($result->isEstimated());
    }
}
