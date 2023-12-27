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

namespace ModelflowAi\Core\Request;

use ModelflowAi\Core\Request\Criteria\AIRequestCriteria;
use ModelflowAi\Core\Request\Criteria\PerformanceRequirement;
use ModelflowAi\Core\Request\Criteria\PrivacyRequirement;

interface AIRequestInterface
{
    public function getCriteria(): AIRequestCriteria;

    public function matches(PrivacyRequirement|PerformanceRequirement $criteria): bool;
}
