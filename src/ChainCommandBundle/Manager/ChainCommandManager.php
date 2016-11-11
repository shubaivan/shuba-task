<?php

namespace ChainCommandBundle\Manager;

use ChainCommandBundle\Chain\Chain;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class ChainCommandManager
{
    const MSG_NOT_MASTER = 'Error: %s command is a member of %s command chain and cannot be executed on its own.';
    const MSG_EMPTY_MASTER = 'Error: %s without master.';
    const MSG_START_CHAIN = '%s is a master command of a command chain that has registered member commands';
    const MSG_CHAIN_MEMBER = '%s registered as a member of %s command chain';
    const MSG_MASTER_ITSELF_FIRST = 'Executing %s command itself first:';
    const MSG_EXECUTE_MEMBERS = 'Executing %s chain members:';
    const MSG_MASTER_COMPLETE = 'Execution of %s chain completed';

    /**
     * @var Chain
     */
    protected $chain;

    /**
     * @var LoggerInterface
     */
    protected $loggerInterface;

    /**
     * CommandSubscriber constructor.
     *
     * @param Chain $chain
     * @param LoggerInterface $loggerInterface
     */
    public function __construct(
        Chain $chain,
        LoggerInterface $loggerInterface
    )
    {
        $this->chain = $chain;
        $this->loggerInterface = $loggerInterface;
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
            $this->loggerInterface->info(sprintf(self::MSG_START_CHAIN, $command->getName()));
            $members = $this->chain->getChainCommand($channel);
            foreach ($members as $member) {
                /** @var Command $member */
                $this->loggerInterface->info(sprintf(self::MSG_CHAIN_MEMBER, $command->getName(), $member->getName()));
            }
            $this->loggerInterface->info(sprintf(self::MSG_MASTER_ITSELF_FIRST, $command->getName()));
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
            $this->loggerInterface->info(sprintf(self::MSG_EXECUTE_MEMBERS, $command->getName()));
            /** @var Command[] $chainCommands */
            foreach ($chainCommands as $chainCommand) {
                $this->doRun(
                    $chainCommand, 
                    new ArrayInput([]), 
                    $output
                );
            }
            $this->loggerInterface->info(sprintf(self::MSG_MASTER_COMPLETE, $command->getName()));
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
        $bufferedOutput = new BufferedOutput();
        $command->run($input, $bufferedOutput);
        $message = trim($bufferedOutput->fetch());

        $this->loggerInterface->info($message);
        $output->writeln($message);
    }
}
