<?php

namespace Orange\SearchBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Claroline\CoreBundle\Entity\IndexableInterface;

/** @param integer $status 

 */
class SearchListener extends ContainerAware
{
	/**
	 * Array of entities to send to the indexer watcher.
	 * We can't send them directly in the postPersist method as the modification
	 * may not be written yet in the database. 
	 * @var array of IndexableInterface
	 */
	private $entityQueue = array();

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
        	$this->entityQueue[] = $event->getEntity();
        }
    }
    
    /**
     * @param PostFlushEventArgs $event
     */
    public function postFlush(PostFlushEventArgs $event)
    {
	    foreach ($this->entityQueue as $entity) {
            $this->container
                 ->get('orange.search.indexer_todo_manager')
                 ->toIndex($entity);
	    }
	    $this->entityQueue = array();
    }

}
