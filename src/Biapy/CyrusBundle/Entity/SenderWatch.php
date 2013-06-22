<?php

namespace Biapy\CyrusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SenderWatch
 */
class SenderWatch
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $sender_address;

    /**
     * @var string
     */
    private $target;

    /**
     * @var boolean
     */
    private $enabled;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->enabled = true;
    }


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
     * Set sender_address
     *
     * @param string $senderAddress
     * @return SenderWatch
     */
    public function setSenderAddress($senderAddress)
    {
        $this->sender_address = $senderAddress;

        return $this;
    }

    /**
     * Get sender_address
     *
     * @return string 
     */
    public function getSenderAddress()
    {
        return $this->sender_address;
    }

    /**
     * Set target
     *
     * @param string $target
     * @return SenderWatch
     */
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Get target
     *
     * @return string 
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return SenderWatch
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean 
     */
    public function getEnabled()
    {
        return $this->enabled;
    }
}
