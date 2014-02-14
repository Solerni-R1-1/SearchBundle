<?php

namespace Orange\SearchBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Orange\SearchBundle\Entity\SyncIndex;
use Claroline\CoreBundle\Entity\IndexableInterface;

/** @param integer $status 

 */
class SearchListener extends ContainerAware
{

    /**
     * @param LifecycleEventArgs $event
     */
    public function preRemove(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();
        if (($entity instanceof IndexableInterface)) {
            $em = $this->container->get('doctrine')->getManager();
            $syncIndex = $em->getRepository('OrangeSearchBundle:SyncIndex')->findOneByEntityId($entity->getId());

            // Status 
            // 1 : Not Indexed
            // 2 : Indexed
            // 3 : Deleted

            if ($syncIndex) {
                if ($syncIndex->getStatus() == 2) {
                    $syncIndex->setStatus(3);
                    $em->persist($syncIndex);
                } else {
                    $em->remove($syncIndex);
                }
                $em->flush();
            }
        }
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postRemove(LifecycleEventArgs $event)
    {
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postUpdate(LifecycleEventArgs $event)
    {
        $this->postPersist($event);
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postPersist(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        $logger = $this->container->get('logger');
        
        if (($entity instanceof IndexableInterface)) {
            
            $logger->info('Persist Entity class name '. get_class($entity));
            $em = $this->container->get('doctrine')->getManager();
            //check if exist -- update
            $syncIndex = $em->getRepository('OrangeSearchBundle:SyncIndex')->findOneByEntityId($entity->getId());
            if (!$syncIndex) {
                $syncIndex = new SyncIndex();
            }
            $syncIndex->setEntityId($entity->getId());

            $syncIndex->setStatus(1);
            $syncIndex->setClassName(get_class($entity));

            $em->persist($syncIndex);
            $em->flush();
        }
    }

    /**
     * @param PostFlushEventArgs $event
     */
    public function postFlush(PostFlushEventArgs $event)
    {
        
    }

}