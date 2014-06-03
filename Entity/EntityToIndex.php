<?php

namespace Orange\SearchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EntityToIndex
 *
 * @ORM\Table(name="orange_search_entity_to_index")
 * @ORM\Entity(repositoryClass="Orange\SearchBundle\Entity\EntityToIndexRepository")
 */
class EntityToIndex
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="class_name", type="string", length=255)
     */
    private $className;

    /**
     * @var boolean
     *
     * @ORM\Column(name="to_index", type="boolean")
     */
    private $toIndex;
 

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set className
     *
     * @param string $className
     * @return EntityToIndex
     */
    public function setClassName($className)
    {
        $this->className = $className;

        return $this;
    }

    /**
     * Get className
     *
     * @return string 
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Set toIndex
     *
     * @param boolean $toIndex
     * @return EntityToIndex
     */
    public function setToIndex($toIndex)
    {
        $this->toIndex = $toIndex;

        return $this;
    }

    /**
     * Get toIndex
     *
     * @return boolean 
     */
    public function getToIndex()
    {
        return $this->toIndex;
    }
}
