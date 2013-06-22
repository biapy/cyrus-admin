<?php

namespace Biapy\CyrusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Domain
 */
class Domain
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $users;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $aliases;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->aliases = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * String representation.
     */
    public function __toString()
    {
      return strval($this->getName());
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
     * Set name
     *
     * @param string $name
     * @return Domain
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add users
     *
     * @param \Biapy\CyrusBundle\Entity\User $users
     * @return Domain
     */
    public function addUser(\Biapy\CyrusBundle\Entity\User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \Biapy\CyrusBundle\Entity\User $users
     */
    public function removeUser(\Biapy\CyrusBundle\Entity\User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add aliases
     *
     * @param \Biapy\CyrusBundle\Entity\Alias $aliases
     * @return Domain
     */
    public function addAlias(\Biapy\CyrusBundle\Entity\Alias $aliases)
    {
        $this->aliases[] = $aliases;

        return $this;
    }

    /**
     * Remove aliases
     *
     * @param \Biapy\CyrusBundle\Entity\Alias $aliases
     */
    public function removeAlias(\Biapy\CyrusBundle\Entity\Alias $aliases)
    {
        $this->aliases->removeElement($aliases);
    }

    /**
     * Get aliases
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAliases()
    {
        return $this->aliases;
    }
    
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $adminUsers;


    /**
     * Add adminUsers
     *
     * @param \Biapy\CyrusBundle\Entity\User $adminUsers
     * @return Domain
     */
    public function addAdminUser(\Biapy\CyrusBundle\Entity\User $adminUsers)
    {
        $this->adminUsers[] = $adminUsers;

        return $this;
    }

    /**
     * Remove adminUsers
     *
     * @param \Biapy\CyrusBundle\Entity\User $adminUsers
     */
    public function removeAdminUser(\Biapy\CyrusBundle\Entity\User $adminUsers)
    {
        $this->adminUsers->removeElement($adminUsers);
    }

    /**
     * Get adminUsers
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAdminUsers()
    {
        return $this->adminUsers;
    }
}
