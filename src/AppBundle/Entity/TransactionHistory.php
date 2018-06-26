<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity()
 * @ORM\Table(name="pay_transaction_history")
 */
class TransactionHistory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="reference_id", type="string")
     */
    private $referenceId;

    /**
     * @ORM\Column(name="status", type="string")
     */
    private $status;

     /**
     * @Gedmo\Timestampable(on="create")
      * @ORM\Column(name="created_at", type="datetime")
      */
    private $createdAt;

    /**
     * @param string $referenceId
     * @param string $status
     */
    public function __construct(string $referenceId, string $status)
    {
        $this->referenceId = $referenceId;
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getReferenceId()
    {
        return $this->referenceId;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
