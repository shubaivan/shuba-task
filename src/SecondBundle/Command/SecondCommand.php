<?php

namespace SecondBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SecondCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('second:hi');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Hi from Second!');
    }
}
