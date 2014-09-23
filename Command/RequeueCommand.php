<?php

namespace Orange\SearchBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * This class contains common methods for the plugin install/uninstall commands.
 */
class RequeueCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:indexer:requeue')
             ->setDescription('Reindexer les entité')
             ->addArgument('className', InputArgument::OPTIONAL, 'Quelles sont les entitées')
             ->addArgument('id', InputArgument::OPTIONAL, 'Id de l\'entité');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $indexerTodoManager = $this->getContainer()->get('orange.search.indexer_todo_manager');

        $className = $input->getArgument('className');
        $id = $input->getArgument('id');

        if ($className) {
            $oReflectionClass = new \ReflectionClass($className);
            if ($oReflectionClass->implementsInterface('Claroline\CoreBundle\Entity\IndexableInterface')) {
                if ($id) {
                    $output->writeln('Index ' . $className . ' id:' . $id . ' Entity');
                    $report = $indexerTodoManager->requeueOne($className, $id);
                    $output->writeln($report);
                    
                } else {
                    $output->writeln('Index all ' . $className . ' Entities');
                    $reports = $indexerTodoManager->requeue($className);
                    $this->printReport($reports, $output);
                }
            }
        } else {
            $reports = $indexerTodoManager->requeueAll();
            $this->printReport($reports, $output);
        }

        $output->writeln('Done !');
    }

    private function printReport($reports, $output)
    {
        foreach ($reports as $report) {
            $output->writeln($report);
        }
    }

}
