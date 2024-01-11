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

namespace ModelflowAi\Mistral\Tests\Unit\Responses;

use ModelflowAi\Mistral\Responses\Usage;
use ModelflowAi\Mistral\Tests\DataFixtures;
use PHPUnit\Framework\TestCase;

final class UsageTest extends TestCase
{
    public function testFrom(): void
    {
        $usageData = DataFixtures::CHAT_CREATE_RESPONSE['usage'];

        $usage = Usage::from($usageData);

        $this->assertInstanceOf(Usage::class, $usage);
        $this->assertSame($usageData['prompt_tokens'], $usage->promptTokens);
        $this->assertSame($usageData['completion_tokens'], $usage->completionTokens);
        $this->assertSame($usageData['total_tokens'], $usage->totalTokens);
    }
}
