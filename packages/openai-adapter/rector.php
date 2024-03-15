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

use Rector\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector;
use Rector\Config\RectorConfig;
use Rector\Php81\Rector\Array_\FirstClassCallableRector;

return static function (RectorConfig $rectorConfig): void {
    $config = require __DIR__ . '/../../rector.php';
    $config($rectorConfig, __DIR__);

    $rectorConfig->skip([
        CallableThisArrayToAnonymousFunctionRector::class => [
            __DIR__ . '/tests/Unit/Model/OpenaiChatModelAdapterTest.php',
        ],
        FirstClassCallableRector::class => [
            __DIR__ . '/tests/Unit/Model/OpenaiChatModelAdapterTest.php',
        ],
    ]);
};
