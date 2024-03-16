#!/usr/bin/env php
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

require \dirname(__DIR__) . '/vendor/autoload.php';

use App\Command\GenerateCommand;
use Symfony\Component\Console\Application;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

$twig = new Environment(new FilesystemLoader(\dirname(__DIR__) . '/templates'));
$twig->addFilter(new TwigFilter('break_text', function ($string, $length = 120) {
    $pattern = '/(.{1,' . $length . '})([ \n]{1}|$)/';
    $replacement = '$1' . \PHP_EOL;

    return \preg_replace($pattern, $replacement, (string) $string);
}));
$application = new Application();

$application->add(new GenerateCommand($twig));
$application->setDefaultCommand('generate', true);

$application->run();
