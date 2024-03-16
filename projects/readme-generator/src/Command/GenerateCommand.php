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

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Inflector\Inflector;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;

class GenerateCommand extends Command
{
    public function __construct(
        private readonly Environment $twig,
    ) {
        parent::__construct('generate');
    }

    protected function configure(): void
    {
        $this->setDescription('Generate readme files for all packages');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Generating readme files');

        $rootPath = \dirname(__DIR__, 4);
        $packagePaths = \glob($rootPath . '/*/*');
        if (false === $packagePaths) {
            throw new \RuntimeException('Could not find any package.');
        }

        $packages = [];
        foreach ($packagePaths as $packagePath) {
            if (!\is_file($packagePath . '/.readme.yaml')) {
                continue;
            }

            $package = \basename($packagePath);
            $typeNames = Inflector::singularize(\basename(\dirname($packagePath)));
            $type = \is_array($typeNames) ? \end($typeNames) : $typeNames;
            /** @var array{
             *     title: string,
             *     type: string,
             *     package?: string,
             *     description: string,
             * } $config
             */
            $config = Yaml::parse((string) \file_get_contents($packagePath . '/.readme.yaml'));
            $config['package'] ??= $package;
            $config['type'] = $type;

            $io->section($config['title'] . ' ' . $type);
            $io->text('Generating readme for ' . $package);

            $content = $this->twig->render($type . '.md.twig', $config);
            \file_put_contents($packagePath . '/README.md', $content);

            $io->success('Readme generated');

            $packages[] = $config;
        }

        $io->section('Public Readme');

        /** @var array{
         *     title: string,
         *     description: string,
         * } $config
         */
        $config = Yaml::parse((string) \file_get_contents($rootPath . '/.readme.yaml'));
        $config['packages'] = $packages;
        $content = $this->twig->render('public-readme.md.twig', $config);
        \file_put_contents($rootPath . '/README.md', $content);

        $io->success('Readme generated');

        $io->section('Profile Readme');

        /** @var array{
         *     title: string,
         *     description: string,
         * } $config
         */
        $config = Yaml::parse((string) \file_get_contents($rootPath . '/.readme.yaml'));
        $config['packages'] = $packages;
        $content = $this->twig->render('profile.md.twig', $config);
        \file_put_contents($rootPath . '/profile/README.md', $content);

        $io->success('Readme generated');

        return self::SUCCESS;
    }
}
