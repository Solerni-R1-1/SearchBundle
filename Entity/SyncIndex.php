<?php

namespace Orange\SearchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SyncIndex
 *
 * @ORM\Table(name="orange_search_sync_index")
 * @ORM\Entity(repositoryClass="Orange\SearchBundle\Entity\SyncIndexRepository")
 */
class SyncIndex
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
     * @var integer
     *
     * @ORM\Column(name="entity_id", type="integer", nullable=true)
     */
    private $entityId;
    
    /**
     * @var string
     *
     * @ORM\Column(name="document_id", type="string", length=255, nullable=true)
     */
    private $documentId;

    /**
     * @var string
     *
     * @ORM\Column(name="class_name", type="string", length=255)
     */
    private $className;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status;


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
     * Set entityId
     *
     * @param integer $entityId
     * @return SyncIndex
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * Get entityId
     *
     * @return integer 
     */
    public function getEntityId()
    {
        return $this->entityId;
    }
    /**
     * Set documentId
     *
     * @param string $documentId
     * @return SyncIndex
     */
    public function setDocumentId($documentId)
    {
        $this->documentId = $documentId;

        return $this;
    }

    /**
     * Get documentId
     *
     * @return string 
     */
    public function getDocumentId()
    {
        return $this->documentId;
    }
    
    /**
     * Set className
     *
     * @param string $className
     * @return SyncIndex
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
     * Set status
     *
     * @param integer $status
     * @return SyncIndex
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer 
     */
    public function getStatus()
    {
        return $this->status;
    }
}
