<?php

namespace ChainCommandBundle\Tests\Func;

use ChainCommandBundle\Chain\Chain;
use ChainCommandBundle\Tests\TestMasterCommand;
use ChainCommandBundle\Tests\TestMemberCommand;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;

class ChainCommandTest extends KernelTestCase
{
    const TEST_MASTER_NAME = 'test:master-test';
    const TEST_MEMBER_NAME = 'test:member-test';
    const TEST_CHANNEL = 'test';

    /** @var  Console\Application $application */
    protected $application;
    protected $container;
    
    /** @var BufferedOutput $output */
    protected $output;

    protected function setUp()
    {
        static::bootKernel();
        $this->container = static::$kernel->getContainer();

        $this->application = new Console\Application(static::$kernel);
        $this->application->setAutoExit(false);
        $master = new TestMasterCommand();
        $member = new TestMemberCommand();
        $this->application->add($master);
        $this->application->add($member);

        /** @var Chain $chain */
        $chain = $this->container->get('chaincommandbundle.chain');
        $chain->registerCommand($master, true, self::TEST_CHANNEL);
        $chain->registerCommand($member, false, self::TEST_CHANNEL);
    }
    
    public function setUpMaster() {
        $this->output = new BufferedOutput();
        $this->application->run(new ArgvInput([
            './bin/console',
            self::TEST_MASTER_NAME,
        ]), $this->output);
    }

    public function setUpMember() {
        $this->output = new BufferedOutput();
        $this->application->run(new ArgvInput([
            './bin/console',
            self::TEST_MEMBER_NAME,
        ]), $this->output);
    }

    public function testGetMasterOutput()
    {
        $this->setUpMaster();
        $output = $this->output->fetch();
        $equals = "master execute called\nmember execute called\n";
        $this->assertEquals($equals, $output);
    }

    public function testGetMemberOutput()
    {
        $this->setUpMember();
        $output = $this->output->fetch();
        $equals = sprintf(
            "Error: %s command is a member of %s command chain and cannot be executed on its own.\n",
            self::TEST_MEMBER_NAME,
            self::TEST_MASTER_NAME
        );
        
        $this->assertEquals($equals, $output);
    }
}