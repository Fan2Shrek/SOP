#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/Sop.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

$application = new Application();
$application->add(new class extends Command {
    protected function configure(): void
    {
        $this
            ->addOption('debug', 'd', null, 'Enable debug mode')
            ->addOption('register', 'r', null, 'How many registers to use')
            ->addOption('export', 'e', null, 'Export registers')
            ->addOption('execute', 'E', InputOption::VALUE_REQUIRED, 'Execute the code')
            ->addOption('ram', null, InputOption::VALUE_REQUIRED, 'RAM size (0 to work without RAM, default 256)', 256)
            ->addArgument('file', InputArgument::OPTIONAL, 'File to execute', null)
            ->setHelp('This command runs the Sop interpreter.')
            ->setName('run')
            ->setDescription('Run the Sop interpreter')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sop = new Sop(
            $input->getOption('register') ?: 8,
        );

        $memory = null;
        if (0 !== $input->getOption('ram')) {
            $memory = new Memory($input->getOption('ram'));
            $sop->addMemory($memory);
        }

        if ($input->getOption('debug')) {
            $sop->enableDebug();
        }

        if ($input->getArgument('file')) {
            if (!is_readable($input->getArgument('file'))) {
                $output->writeln('<error>File not found</error>');
                return Command::FAILURE;
            }

            $code = file_get_contents($input->getArgument('file'));
        } elseif ($input->getOption('execute')) {
            $code = str_replace('\n', "\n", $input->getOption('execute'));
        } else {
            $code = <<<CODE
LOAD 5 6 0
LOAD 0 10 0

# ADD
ADD 5 0 2

SUB_2 2 5 2

JMP 24 0 0

# SKIP
FAKE 0 0 0

ADD_1 0 2 9
HALT 0 0 0
CODE;
        }

        try {
            $sop->process($code);
        } finally {
            if ($input->getOption('export') || $input->getOption('debug')) {
                $output->writeln('<info>Registers:</info>');
                foreach ($sop->exportRegisters() as $index => $value) {
                    $output->writeln("R$index: $value");
                }

                if ($memory) {
                    $output->writeln('<info>Memory:</info>');
                    foreach ($memory->export() as $index => $value) {
                        if (0 === $value) {
                            continue;
                        }

                        $output->writeln("M$index: $value");
                    }
                }
            }
        }

        return Command::SUCCESS;
    }
});

$application->setDefaultCommand('run', true);

$application->run();
