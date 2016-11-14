<?php

namespace ChainCommandBundle\Tests;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestMemberCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('test:member-test');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('member execute called');
    }
}
