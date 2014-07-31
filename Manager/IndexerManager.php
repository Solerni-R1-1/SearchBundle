<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Orange\SearchBundle\Manager;

use Claroline\CoreBundle\Entity\Event;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Exception\Exception;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Orange\SearchBundle\Manager\IndexerTodoManager;
use Solarium\Core\Client\Client;
use Orange\SearchBundle\Entity\EntityToIndex;

/**
 * @DI\Service("orange.search.indexer_manager")
 */
class IndexerManager
{

    private $entityManager;
    private $logger;
    private $security;
    private $indexerTodoManager;
    private $solariumClient;
    
    /**
     * @DI\InjectParams({
     *     "entityManager"      = @DI\Inject("doctrine.orm.entity_manager"),
     *     "logger"             = @DI\Inject("logger"),
     *     "security"           = @DI\Inject("security.context"),
     *     "indexerTodoManager" = @DI\Inject("orange.search.indexer_todo_manager"),
     *     "solariumClient"     = @DI\Inject("solarium.client")
     *     
     * })
     */
    public function __construct(
        EntityManager $entityManager,
        Logger $logger, 
        SecurityContextInterface $security, 
        IndexerTodoManager $indexerTodoManager, 
        Client $solariumClient
    )
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->security = $security;
        $this->indexerTodoManager = $indexerTodoManager;
        $this->solariumClient = $solariumClient;
    }

    public function process($message)
    {
        $report = 'No action match';

        switch ($message['action']) {
            case 'index':
                $entity = $this->entityManager
                            ->getRepository($message['class_name'])
                            ->find($message['entity_id']);

                
                $update = $this->solariumClient->createUpdate();
                $doc    = $update->createDocument();

                // fill the index document
                $doc    = $entity->fillIndexableDocument($doc);

                $update->addDocuments(array($doc));
                $update->addCommit();

                // this executes the query and returns the result
                $result = $this->solariumClient->update($update);

                // If document is indexed, change the status of SyncIndex entry
                if ($result->getStatus() == 0) {
                    $report = 'Document ' . $doc->id . ' indexed';
                }
                break;
                
            case 'delete':
                // We delete the ressource
                $update = $this->solariumClient->createUpdate();
                $update->addDeleteQuery('id:' . $message['document_id']);
                $update->addCommit();
                $result = $this->solariumClient->update($update);

                // If document is Deleted, remove SyncIndex entry
                if ($result->getStatus() == 0) {
                    $report = 'Document ' . $message['document_id'] . ' deleted';
                }
                break;

            case 'delete-all':
                // We delete the ressource
                $update = $this->solariumClient->createUpdate();
                $update->addDeleteQuery('id:*');
                $update->addCommit();
                $result = $this->solariumClient->update($update);

                // If document is Deleted, remove SyncIndex entry
                if ($result->getStatus() == 0) {
                    $report = 'Delete all executed';
                }
                break;
                

            default:
                break;
        }
        return $report;
    }
    
    /**
     *  Insert or update entity to index
     * 
     */
    public function persistEntityToIndex($className, $isToIndex)
    {
        $entityToIndex = $this->entityManager->getRepository('OrangeSearchBundle:EntityToIndex')->findOneByClassName($className);
        if (!$entityToIndex) {
            $entityToIndex = new EntityToIndex();
        }
        $entityToIndex->setClassName($className);
        $entityToIndex->setToIndex($isToIndex);

        $this->entityManager->persist($entityToIndex);
    }

    /*
     * Get all Indexable entities
     */

    public function getAllIndexableEntities()
    {
        //all entities
        $entityNames = $this->entityManager->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();
        $choices     = array();

        foreach ($entityNames as $entityName) {
            if (array_key_exists('Claroline\CoreBundle\Entity\IndexableInterface', class_implements($entityName))) {
                $choices [$entityName] = $entityName;
            }
        }

        return $choices;
    }



}
