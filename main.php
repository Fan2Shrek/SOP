#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';
require __DIR__.'/Sop.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

$application = new Application();
$application->add(new class extends Command {
    protected function configure(): void
    {
        $this
            ->addOption('debug', 'd', null, 'Enable debug mode')
            ->addOption('register', 'r', null, 'How many registers to use')
            ->addOption('export', 'e', null, 'Export registers')
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

        if ($input->getOption('debug')) {
            $sop->enableDebug();
        }

        if ($input->getArgument('file') && is_readable($input->getArgument('file'))) {
            $code = file_get_contents($input->getArgument('file'));
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

        $sop->process($code);

        if ($input->getOption('export')) {
            $output->writeln('<info>Registers:</info>');
            foreach ($sop->exportRegisters() as $index => $value) {
                $output->writeln("R$index: $value");
            }
        }

        return Command::SUCCESS;
    }
});

$application->setDefaultCommand('run', true);

$application->run();
