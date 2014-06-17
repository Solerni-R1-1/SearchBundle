<?php

namespace Orange\SearchBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;

/** @param integer $status 

 */
class SearchListener extends ContainerAware
{

    /**
     * @param LifecycleEventArgs $event
     */
    public function preRemove(LifecycleEventArgs $event)
    {
        if (($event->getEntity() instanceof IndexableInterface)) {
            $this->container
                 ->get('orange.search.indexer_todo_manager')
                 ->toDelete($event->getEntity());
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
        if (($event->getEntity() instanceof IndexableInterface)) {
            $this->container
                 ->get('orange.search.indexer_todo_manager')
                 ->toIndex($event->getEntity());
        }
    }

    /**
     * @param PostFlushEventArgs $event
     */
    public function postFlush(PostFlushEventArgs $event)
    {
        
    }

}
