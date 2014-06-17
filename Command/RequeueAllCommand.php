<?php

namespace Orange\SearchBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * This class contains common methods for the plugin install/uninstall commands.
 */
class RequeueAllCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:indexer:requeue')
             ->setDescription('Reindexer toute les entité');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
       $indexerManager = $this->getContainer()->get('orange.search.indexer_manager');
       $indexerManager->requeueAll();
       foreach ($indexerManager->getReports() as $report) {
           $output->writeln($report);
       }
    }

}
