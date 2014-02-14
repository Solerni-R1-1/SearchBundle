<?php

namespace Orange\SearchBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * This class contains common methods for the plugin install/uninstall commands.
 */
class IndexerCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:indexer:sync')
             ->setDescription('Synchronise db solr entities');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $configSolr = $this->getConfig();
        //$client = $this->getContainer()->get('solarium.client');
        $client = new \Solarium\Client($configSolr);
        
        // index all entries
        $toIndexList = $em->getRepository('OrangeSearchBundle:SyncIndex')->findByStatus(1);
        
        foreach ($toIndexList as $toIndex) {
            $entity = $em->getRepository($toIndex->getClassName())->find($toIndex->getEntityId());

            $update = $client->createUpdate();
            $doc = $update->createDocument();
            
            // fill the index document
            $doc = $entity->fillIndexableDocument($doc);
          
            $update->addDocuments(array($doc));
            $update->addCommit();
            
            // this executes the query and returns the result
            $result = $client->update($update);
            
            // If document is indexed, change the status of SyncIndex entry
            if ($result->getStatus() == 0) {
                // Status
                // 1 : Not Indexed
                // 2 : Indexed
                // 3 : Deleted
                $toIndex->setStatus(2);
                $toIndex->setDocumentId($doc->id);
                $em->persist($toIndex);
                $output->writeln('Document '. $doc->id .' indexed');
            }
        }
        
        // Delete
        $toDeleteList = $em->getRepository('OrangeSearchBundle:SyncIndex')->findByStatus(3);
        foreach ($toDeleteList as $toDelete) {

            // We delete the ressource
            $update = $client->createUpdate();
            $update->addDeleteQuery('id:'.$toDelete->getDocumentId());
            $update->addCommit();
            $result = $client->update($update);

            // If document is Deleted, remove SyncIndex entry
            if ($result->getStatus() == 0) {
                $output->writeln('Document '. $toDelete->getDocumentId() .' deleted');
                $em->remove($toDelete);
            }
        }
        
        $em->flush();
        $output->writeln('Done');
    }
    
    /*
     * get solr host config
     * 
     * @return array config
     */
    private function getConfig()
    {
        return array(
            'endpoint' => array(
                'localhost' => array(
                    'host' => $this->getContainer()->getParameter('solr.host'),
                    'port' => $this->getContainer()->getParameter('solr.port'),
                    'path' => $this->getContainer()->getParameter('solr.path')
                )
            )
        );
    }

}
