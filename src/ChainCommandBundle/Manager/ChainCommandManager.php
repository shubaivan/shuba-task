<?php

namespace ChainCommandBundle\Manager;

use ChainCommandBundle\Chain\Chain;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class ChainCommandManager
{
    const MSG_NOT_MASTER = 'Error: %s command is a member of %s command chain and cannot be executed on its own.';
    const MSG_EMPTY_MASTER = 'Error: %s without master.';

    /**
     * @var Chain
     */
    protected $chain;

    /**
     * CommandSubscriber constructor.
     *
     * @param Chain $chain
     */
    public function __construct(Chain $chain)
    {
        $this->chain = $chain;
    }

    /**
     * Getter for chain.
     * 
     * @return Chain
     */
    public function getChain()
    {
        return $this->chain;
    }

    /**
     * Run chain from master.
     * 
     * @param Command         $command
     * @param string          $channel
     * @param InputInterface  $input
     * @param OutputInterface $output
     * 
     * @return bool
     */
    public function runMasterCommand(
        Command $command,
        $channel,
        InputInterface $input,
        OutputInterface $output
    ) {
        $master = $this->getChain()->getMaster($channel);
        if ($master && $master->getName() === $command->getName()) {
            $this->doRun($command, $input, $output);

            return true;
        } elseif (!$master) {
            $output->writeln(
                sprintf(
                    self::MSG_EMPTY_MASTER,
                    $channel
                )
            );
        } else {
            $output->writeln(
                sprintf(
                    self::MSG_NOT_MASTER,
                    $command->getName(),
                    $master->getName()
                )
            );

            return false;
        }
    }

    /**
     * Run rest of the chain.
     * 
     * @param Command         $command
     * @param string          $channel
     * @param OutputInterface $output
     * 
     * @return bool
     */
    public function runChainCommands(
        Command $command,
        $channel,
        OutputInterface $output
    ) {
        $member = $this->getChain()->isMember($command, $channel);
        if ($member) {
            return false;
        }

        $chainCommands = $this->getChain()->getChainCommand($channel);
        if (count($chainCommands)) {
            /** @var Command[] $chainCommands */
            foreach ($chainCommands as $chainCommand) {
                $this->doRun($chainCommand, new ArrayInput([]), $output);
            }
        }

        return true;
    }

    /**
     * Replace default output to BufferedOutput for logging and console output together.
     *
     * @param Command         $command
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    protected function doRun(Command $command, InputInterface $input, OutputInterface $output)
    {
        //        $bufferedOutput = new BufferedOutput();
        $command->run($input, $output);
//
//        $message = trim($bufferedOutput->fetch());
//        $this->logger->info($message);
//        $output->writeln($message);
    }
}
