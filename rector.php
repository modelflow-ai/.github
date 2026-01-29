<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig, string $directory): void {
    $rectorConfig->paths([
        $directory,
    ]);

    $rectorConfig->skip([
        $directory . '/vendor',
    ]);

    $rectorConfig->bootstrapFiles([
        $directory . '/vendor/autoload.php',
    ]);

    $rectorConfig->autoloadPaths([
        $directory . '/src',
        $directory . '/tests',
    ]);

    $rectorConfig->phpstanConfig($directory . '/phpstan.neon');

    // $rectorConfig->importNames();
    // $rectorConfig->importShortClasses(false);

    $rectorConfig->sets([
        SetList::CODE_QUALITY,
        LevelSetList::UP_TO_PHP_82,
        PHPUnitSetList::PHPUNIT_100,
    ]);
};
