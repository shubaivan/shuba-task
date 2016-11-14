<?php

namespace ChainCommandBundle\Tests;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestMasterCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('test:master-test');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('master execute called');
    }
}
