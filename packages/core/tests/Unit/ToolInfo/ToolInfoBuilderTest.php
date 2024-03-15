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

namespace ModelflowAi\Core\Tests\Unit\ToolInfo;

use ModelflowAi\Core\ToolInfo\ToolInfoBuilder;
use ModelflowAi\Core\ToolInfo\ToolTypeEnum;
use PHPUnit\Framework\TestCase;

class ToolInfoBuilderTest extends TestCase
{
    public function testBuildToolInfo(): void
    {
        $toolInfo = ToolInfoBuilder::buildToolInfo($this, 'toolMethod', 'test', ToolTypeEnum::FUNCTION);

        $this->assertSame(ToolTypeEnum::FUNCTION, $toolInfo->type);
        $this->assertSame('test', $toolInfo->name);
        $this->assertSame('This is a description.', $toolInfo->description);
        $this->assertCount(2, $toolInfo->parameters);
        $this->assertSame('required', $toolInfo->parameters[0]->name);
        $this->assertSame('this is a required parameter', $toolInfo->parameters[0]->description);
        $this->assertSame('optional', $toolInfo->parameters[1]->name);
        $this->assertSame('this is an optional parameter', $toolInfo->parameters[1]->description);
        $this->assertCount(1, $toolInfo->requiredParameters);
        $this->assertSame('required', $toolInfo->parameters[0]->name);
        $this->assertSame('this is a required parameter', $toolInfo->parameters[0]->description);
    }

    /**
     * This is a description.
     *
     * @param string $required this is a required parameter
     * @param string $optional this is an optional parameter
     */
    public function toolMethod(string $required, string $optional = ''): string
    {
        return $required . $optional;
    }
}
