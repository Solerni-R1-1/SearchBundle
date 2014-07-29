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

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Entity\IndexableInterface;
use Orange\SearchBundle\Entity\SyncIndex;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * @DI\Service("orange.search.indexer_todo_manager")
 */
class IndexerTodoManager
{

    private $entityManager;
    private $security;
    private $logger;
    private $sender;

    /**
     * @DI\InjectParams({
     *     "entityManager"      = @DI\Inject("doctrine.orm.entity_manager"),
     *     "logger"             = @DI\Inject("logger"),
     *     "security"           = @DI\Inject("security.context")
     * })
     */
    public function __construct(
        EntityManager $entityManager, 
        Logger $logger, 
        SecurityContextInterface $security
    )
    {
        $this->entityManager = $entityManager;
        $this->logger        = $logger;
        $this->security      = $security;
        
        $ctx                 = new \ZMQContext();
        $this->sender        = new \ZMQSocket($ctx, \ZMQ::SOCKET_PUSH);
        $this->sender->connect("tcp://localhost:11111");
    }

    /*
     * Add entity to the queue with index or update action
     * 
     * @param indexable entity
     */
    public function toIndex(IndexableInterface $entity)
    {
        $this->todo($entity, 'index');
    }

    /*
     * Add entity to the queued with delete action
     * 
     * @param indexable entity
     */
    public function toDelete(IndexableInterface $entity)
    {
        $this->todo($entity, 'delete');
    }
    
     /*
     * Add entity to the queue with index or remove action
     * 
     * @param indexable entity
     */
    private function todo($entity, $action)
    {
        try {
            $className = ClassUtils::getClass($entity);
            if ($this->entityManager
                    ->getRepository('OrangeSearchBundle:EntityToIndex')
                    ->isToIndex($className)) {
                
                $this->logger->info('Send '.$action.' message' . $className);
                
                $message = array(
                    'entity_id'   => $entity->getId(),
                    'class_name'  => $className,
                    'document_id' => $entity->getIndexableDocId(),
                    'action'      => $action
                );
                $this->sender->send(json_encode($message));
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
    
    
    public function requeue($entityToIndexClassName)
    {
        
        $reports         = array();
        $entitiesToIndex = $this->entityManager->getRepository($entityToIndexClassName)->findAll();
        
        foreach ($entitiesToIndex as $entityToIndex) {
            $this->toIndex($entityToIndex);
            $reports [] = "Push " . $entityToIndex->getIndexableDocId();
        }
        
        return $reports;
    }

    
    public function requeueOne($entityToIndexClassName, $id)
    {

        $entityToIndex = $this->entityManager->getRepository($entityToIndexClassName)->findOneById($id);
        $this->toIndex($entityToIndex);
        
        return "Push " . $entityToIndex->getIndexableDocId();
    }

    
    public function requeueAll()
    {
        
        $reports = array();
        $entityToIndexClassNames = $this->entityManager
                                        ->getRepository('OrangeSearchBundle:EntityToIndex')
                                        ->findEntityToIndexClassNames();
        foreach ($entityToIndexClassNames as $entityToIndexClassName) {
            $reports = $reports + $this->requeue($entityToIndexClassName);
        }
        
        return $reports;
    }
    
    
    public function deleteAll()
    {
        try {
            $message = array('action' => 'delete-all');
            $this->sender->send(json_encode($message));
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

}