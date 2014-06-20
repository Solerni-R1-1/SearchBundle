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
                ->setDescription('Watch synchronise db solr entities');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ctx = new \ZMQContext();
        $server = new \ZMQSocket($ctx, \ZMQ::SOCKET_PULL);
        $server->bind("tcp://*:11111");

        while (true) {
            $message = $server->recv();
            $indexerManager = $this->getContainer()->get('orange.search.indexer_manager');
            $messageArray = json_decode($message, true);
            $indexerManager->process($messageArray);
            $output->writeln($messageArray['action'] . ' ' . $messageArray['document_id']);
        }
    }

}
