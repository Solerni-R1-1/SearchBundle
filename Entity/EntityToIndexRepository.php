<?php

namespace Orange\SearchBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * EntityToIndexRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EntityToIndexRepository extends EntityRepository
{

    public function isToIndex($className)
    {   
        $entity = $this->findOneByClassName($className);
        if ($entity) {
            return $entity->getToIndex();
        } else {
            return false;
        }
    }
}
