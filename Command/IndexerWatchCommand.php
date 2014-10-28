<?php

namespace Orange\SearchBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * This class contains common methods for the plugin install/uninstall commands.
 */
class IndexerWatchCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:indexer:watch')
             ->setDescription('Wait for entities to index');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

    	$em = $this->getContainer()->get('doctrine')->getManager();
    	$em->getConnection()->close();
    	
        $ctx = new \ZMQContext();
        $server = new \ZMQSocket($ctx, \ZMQ::SOCKET_PULL);
        $server->bind("tcp://*:11111");

        while (true) {
            $message = $server->recv();

            $em = $this->getContainer()->get('doctrine')->getManager();
            $em->getConnection()->connect();
            
            $indexerManager = $this->getContainer()->get('orange.search.indexer_manager');
            $messageArray = json_decode($message, true);
            $report = $indexerManager->process($messageArray);
            $output->writeln($report);

            $em->getConnection()->close();
        }
    }

}
