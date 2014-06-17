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
use Symfony\Component\Config\Definition\Exception\Exception;
use Claroline\CoreBundle\Entity\IndexableInterface;
use Orange\SearchBundle\Entity\SyncIndex;
use Doctrine\Common\Util\ClassUtils;

/**
 * @DI\Service("orange.search.indexer_todo_manager")
 */
class IndexerTodoManager
{

    private $entityManager;
    private $security;
    private $logger;

    /**
     * @DI\InjectParams({
     *     "entityManager"      = @DI\Inject("doctrine.orm.entity_manager"),
     *     "logger"             = @DI\Inject("logger"),
     *     "security"           = @DI\Inject("security.context")
     * })
     */
    public function __construct(
    \Doctrine\ORM\EntityManager $entityManager, \Symfony\Bridge\Monolog\Logger $logger, \Symfony\Component\Security\Core\SecurityContextInterface $security
    )
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->security = $security;
    }

    /* public function reindexAll()
      {

      foreach ($this->getEntityToIndexClassNames() as $entityToIndexClassName) {
      $entitiesToIndex = $this->entityManager->getRepository($entityToIndexClassName);
      foreach ($entitiesToIndex as $entityToIndex) {
      $entityToIndex;
      }
      }
      } */

    public function toIndex($entity)
    {
        try {
            $className = ClassUtils::getClass($entity);
            $isToIndex = $this->entityManager
                    ->getRepository('OrangeSearchBundle:EntityToIndex')
                    ->isToIndex($className);

            if (($entity instanceof IndexableInterface) && $isToIndex) {

                $this->logger->info('Persist Indexable Entity ' . $className);
                //check if exist -- update
                $syncIndex = $this->entityManager
                        ->getRepository('OrangeSearchBundle:SyncIndex')
                        ->findOneBy(array(
                    'entityId' => $entity->getId(),
                    'className' => $className
                ));

                if (!$syncIndex) {
                    $syncIndex = new SyncIndex();
                }
                $syncIndex->setEntityId($entity->getId());

                $syncIndex->setStatus(1);
                $syncIndex->setClassName($className);

                $this->entityManager->persist($syncIndex);
                $this->entityManager->flush();
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function toDelete($entity)
    {

        try {
            $className = ClassUtils::getClass($entity);
            $isToIndex = $this->entityManager
                    ->getRepository('OrangeSearchBundle:EntityToIndex')
                    ->isToIndex($className);

            if (($entity instanceof IndexableInterface) && $isToIndex) {
                $this->logger->info('Remove Indexable Entity ' . $className);
                $syncIndex = $this->entityManager
                        ->getRepository('OrangeSearchBundle:SyncIndex')
                        ->findOneBy(
                        array(
                            'entityId' => $entity->getId(),
                            'className' => $className
                ));
                // Status 
                // 1 : Not Indexed
                // 2 : Indexed
                // 3 : Deleted

                if ($syncIndex) {
                    //$logger->info('Sync index status : ' .$syncIndex->getStatus());
                    if ($syncIndex->getStatus() == 2) {
                        $syncIndex->setStatus(3);
                        $this->entityManager->persist($syncIndex);
                    } else {
                        $this->entityManager->remove($syncIndex);
                    }
                    $this->entityManager->flush();
                } else {
                    $this->logger->info('No Indexable Entity to remove ' . $className . ' id => ' . $entity->getId());
                }
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
    
}