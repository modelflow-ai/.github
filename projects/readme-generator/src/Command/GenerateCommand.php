<?php

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
        private Environment $twig,
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

        foreach (glob(dirname(__DIR__, 4) . '/*/*') as $packagePath) {
            if (!\is_file($packagePath . '/.readme.yaml')) {
                continue;
            }

            $package = \basename($packagePath);
            $typeNames = Inflector::singularize(\basename(\dirname($packagePath)));
            $type = \end($typeNames);
            $config = Yaml::parse(\file_get_contents($packagePath . '/.readme.yaml'));
            $config['package'] = $package;
            $config['type'] = $type;

            $io->section($config['title'] . ' ' . $type);
            $io->text('Generating readme for ' . $package);

            $content = $this->twig->render('readme.md.twig', $config);
            \file_put_contents($packagePath . '/README.md', $content);

            $io->success('Readme generated');
        }

        return self::SUCCESS;
    }
}
