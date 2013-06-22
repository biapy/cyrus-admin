<?php

namespace Biapy\CyrusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 */
class User
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var boolean
     */
    private $enabled;

    /**
     * @var boolean
     */
    private $has_mailbox;

    /**
     * @var \Biapy\CyrusBundle\Entity\Domain
     */
    private $domain;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->enabled = true;
        $this->has_mailbox = true;
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
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return User
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

    /**
     * Set has_mailbox
     *
     * @param boolean $hasMailbox
     * @return User
     */
    public function setHasMailbox($hasMailbox)
    {
        $this->has_mailbox = $hasMailbox;

        return $this;
    }

    /**
     * Get has_mailbox
     *
     * @return boolean 
     */
    public function getHasMailbox()
    {
        return $this->has_mailbox;
    }

    /**
     * Set domain
     *
     * @param \Biapy\CyrusBundle\Entity\Domain $domain
     * @return User
     */
    public function setDomain(\Biapy\CyrusBundle\Entity\Domain $domain = null)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Get domain
     *
     * @return \Biapy\CyrusBundle\Entity\Domain 
     */
    public function getDomain()
    {
        return $this->domain;
    }
}
