<?php

namespace ChainCommandBundle\Chain;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * Class Chain.
 */
class Chain
{
    /**
     * @var array
     */
    protected $chains;

    /**
     * @var array
     */
    protected $masterCommands;

    /**
     * Chain constructor.
     */
    public function __construct()
    {
        $this->chains = [];
        $this->masterCommands = [];
    }

    /**
     * Register new commands for chain.
     *
     * @param Command $command
     * @param bool    $master
     * @param string  $channel
     */
    public function registerCommand(Command $command, $master, $channel, $weight = 0)
    {
        if ($master) {
            if (isset($this->masterCommands[$channel])) {
                throw new InvalidArgumentException(
                    sprintf(
                        '%s can\'t to be master for the chain. %s already master for the channel %s.',
                        get_class($command),
                        get_class($this->masterCommands[$channel]),
                        $channel
                    )
                );
            }
            $this->masterCommands[$channel] = $command;
        } else {
            $this->chains[$channel][$weight][] = $command;
        }
    }

    /**
     * Return channel of current command.
     *
     * @param Command $command
     *
     * @return string|null
     */
    public function getChannel(Command $command)
    {
        foreach ($this->masterCommands as $channel => $masterCommand) {
            if ($masterCommand->getName() === $command->getName()) {
                return $channel;
            }
        }

        foreach ($this->chains as $channel => $chain) {
            foreach ($chain as $arr) {
                foreach ($arr as $member) {
                    if ($member->getName() === $command->getName()) {
                        return $channel;
                    }
                }
            }
        }

//        foreach ($this->chains as $channel => $chain) {
//            /** @var Command[] $chain */
//            foreach ($chain as $arr) {
//                if ($arr->getName() === $command->getName()) {
//                    return $channel;
//                }
//            }
//        }

        return;
    }

    /**
     * Returns chain master_command command.
     *
     * @param string $channel
     *
     * @return Command|null
     */
    public function getMaster($channel)
    {
        return isset($this->masterCommands[$channel]) ? $this->masterCommands[$channel] : null;
    }

    /**
     * Returns chains command.
     *
     * @param string $channel
     *
     * @return Command[]
     */
    public function getChainCommand($channel)
    {
        $commands = [];

        if (isset($this->chains[$channel])) {
            $chain = $this->chains[$channel];
            ksort($chain, SORT_DESC);
            foreach ($chain as $arr) {
                $commands = array_merge($commands, $arr);
            }
        }

        return $commands;
    }

    /**
     * @param Command $command
     * @param string  $channel
     *
     * @return bool
     */
    public function isMember(Command $command, $channel)
    {
        /** @var Command $arr */
        foreach ($this->chains[$channel] as $arr) {
            foreach ($arr as $member) {
                if ($member->getName() === $command->getName()) {
                    return true;
                }
            }
        }

        return false;
    }
}
