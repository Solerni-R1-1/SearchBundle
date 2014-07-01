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
        $this->logger = $logger;
        $this->security = $security;
        
        $ctx = new \ZMQContext();
        $this->sender = new \ZMQSocket($ctx, \ZMQ::SOCKET_PUSH);
        $this->sender->connect("tcp://localhost:11111");
    }

    /*
     * Add entity to the queue with index or update action
     * 
     * @param indexable entity
     */
    public function toIndex(IndexableInterface $entity)
    {
        
        try {
            $className = ClassUtils::getClass($entity);
            if ($this->entityManager
                    ->getRepository('OrangeSearchBundle:EntityToIndex')
                    ->isToIndex($className)) {

                $this->logger->info('Send index message ' . $className);
                
                $message = array(
                    'entity_id' => $entity->getId(),
                    'class_name' => $className,
                    'document_id'=> $entity->getIndexableDocId(),
                    'action' => 'index'
                );
                $this->sender->send(json_encode($message));
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /*
     * Add entity to the queued with delete action
     * 
     * @param indexable entity
     */
    public function toDelete(IndexableInterface $entity)
    {

        try {
            $className = ClassUtils::getClass($entity);
            if ($this->entityManager
                    ->getRepository('OrangeSearchBundle:EntityToIndex')
                    ->isToIndex($className)) {
                
                $this->logger->info('Send remove message' . $className);
                
                $message = array(
                    'entity_id' => $entity->getId(),
                    'class_name' => $className,
                    'document_id'=> $entity->getIndexableDocId(),
                    'action' => 'delete'
                );
                $this->sender->send(json_encode($message));
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
    
}