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

namespace ModelflowAi\Mistral\Tests\Unit;

use ModelflowAi\Mistral\Model;
use PHPUnit\Framework\TestCase;

final class ModelTest extends TestCase
{
    public function testJsonSupported(): void
    {
        $this->assertTrue(Model::SMALL->jsonSupported());
        $this->assertTrue(Model::MEDIUM->jsonSupported());
        $this->assertTrue(Model::LARGE->jsonSupported());
        $this->assertFalse(Model::TINY->jsonSupported());
    }

    public function testToolsSupported(): void
    {
        $this->assertTrue(Model::LARGE->toolsSupported());
        $this->assertFalse(Model::TINY->toolsSupported());
    }

    public function testSupportedByForKnownModel(): void
    {
        $this->assertTrue(Model::jsonSupportedBy(Model::LARGE->value));
        $this->assertFalse(Model::jsonSupportedBy(Model::TINY->value));
        $this->assertTrue(Model::toolsSupportedBy(Model::LARGE->value));
        $this->assertFalse(Model::toolsSupportedBy(Model::TINY->value));
    }

    public function testSupportedByForModelOutsideTheCatalog(): void
    {
        $this->assertTrue(Model::jsonSupportedBy('zai-glm-5-2'));
        $this->assertTrue(Model::toolsSupportedBy('zai-glm-5-2'));
    }
}
