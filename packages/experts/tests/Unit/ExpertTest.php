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

use ModelflowAi\Core\Request\Criteria\CapabilityCriteria;
use ModelflowAi\Experts\ResponseFormat\JsonSchemaResponseFormat;
use PHPUnit\Framework\TestCase;

class ExpertTest extends TestCase
{
    public function testConstruct(): void
    {
        $expert = new Expert(
            'name',
            'description',
            'instructions',
            [CapabilityCriteria::SMART],
        );

        $this->assertSame('name', $expert->name);
        $this->assertSame('description', $expert->description);
        $this->assertSame('instructions', $expert->instructions);
        $this->assertSame([CapabilityCriteria::SMART], $expert->criteria);
        $this->assertNull($expert->responseFormat);
    }

    public function testConstructWithResponseFormat(): void
    {
        $expert = new Expert(
            'name',
            'description',
            'instructions',
            [CapabilityCriteria::SMART],
            new JsonSchemaResponseFormat([]),
        );

        $this->assertSame('name', $expert->name);
        $this->assertSame('description', $expert->description);
        $this->assertSame('instructions', $expert->instructions);
        $this->assertSame([CapabilityCriteria::SMART], $expert->criteria);
        $this->assertNotNull($expert->responseFormat);
    }
}
