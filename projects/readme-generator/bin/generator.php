#!/usr/bin/env php
<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Command\GenerateCommand;
use Symfony\Component\Console\Application;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

$twig = new Environment(new FilesystemLoader(dirname(__DIR__) . '/templates'));
$twig->addFilter(new TwigFilter('break_text', function ($string, $length = 120) {
    $pattern = '/(.{1,' . $length . '})( +|$)\n?/';
    $replacement = '$1' . PHP_EOL;


    return preg_replace($pattern, $replacement, $string);
}));
$application = new Application();

$application->add(new GenerateCommand($twig));
$application->setDefaultCommand('generate', true);

$application->run();
