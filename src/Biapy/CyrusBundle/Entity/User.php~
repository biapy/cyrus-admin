<?php

namespace Biapy\CyrusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Biapy\SecurityBundle\Entities\Clearence;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Symfony\Component\Security\Core\Util\SecureRandom;

/**
 * User
 */
class User implements UserInterface, AdvancedUserInterface
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
     * @var boolean
     */
    private $is_super_admin;

    /**
     * @var \Biapy\CyrusBundle\Entity\Domain
     */
    private $domain;
	
	/**
     * @var array
     */
    private $roles;
    
    /**
     * @var string
     */
    private $recovery_token;
    
    /**
     * @var \DateTime
     */
    protected $recovery_expiry;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->enabled = true;
        $this->has_mailbox = true;
        $this->role = array('ROLE_USER');
    }
    
    /**
     * String representation.
     */
    public function __toString()
    {
    	return strval(sprintf("%s@%s", $this->getUsername(), $this->getDomain()));
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
     * Set is_super_admin
     *
     * @return boolean
     */
    public function setIsSuperAdmin($isSuperAdmin)
    {
    	$this->is_super_admin = $isSuperAdmin;
    	if($isSuperAdmin){
    		if($this->roles == NULL)
    		{
    			$this->roles = array();
    			$this->addRole('ROLE_SUPER_ADMIN');
    			return $this;
    		}
    		$this->addRole('ROLE_SUPER_ADMIN');
    	} else {
    		$this->removeRole('ROLE_SUPER_ADMIN');
    	}
    	return $this;
    }
    
    public function addRole($role)
    {
    	if(!is_array($this->roles))
    	{
    		$this->roles = array();
    	}
    	if (!in_array($role, $this->roles))
    	{
    		$this->roles[] = $role;
    	}
    
    	return $this;
    }
    
    
    public function removeRole($role)
    {
    	if($this->roles == null)
    	{
    		$this->roles = array('ROLE_USER');
    	}
    	if (false !== $key = array_search(strtoupper($role), $this->roles))
    	{
    		unset($this->roles[$key]);
    	}
    
    	return $this;
    }
    
    
    
    
    /**
     * Get is_super_admin
     *
     * @return boolean
     */
    public function getIsSuperAdmin()
    {
    	return $this->is_super_admin;
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
    
    /**
     * Returns the user roles
     *
     * @return array The roles
     */
    public function getRoles()
    {        
        if($this->getIsSuperAdmin())
        {
        	$this->addRole('ROLE_SUPER_ADMIN');
        }
        
        if($this->getGrantedDomains()->toArray())
        {
        	$this->addRole('ROLE_ADMIN_DOMAIN');
        }
        
        if(sizeof($this->roles) == 0)
        	$this->roles = array('ROLE_USER');
        
        return $this->roles;
    }
        
    public function setRoles(array $roles)
    {
        $this->roles = array_unique(array_filter($roles));

        return $this;
    }
        
   	public function getSalt(){
   		return null;
   	}
    
  
    
    
    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
    }
    
    public function __sleep()
    {
    	return array('id');
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $grantedDomains;

    /**
     * Add grantedDomains
     *
     * @param \Biapy\CyrusBundle\Entity\Domain $grantedDomains
     * @return User
     */
    public function addGrantedDomain(\Biapy\CyrusBundle\Entity\Domain $grantedDomains)
    {
        $this->grantedDomains[] = $grantedDomains;

        return $this;
    }

    /**
     * Remove grantedDomains
     *
     * @param \Biapy\CyrusBundle\Entity\Domain $grantedDomains
     */
    public function removeGrantedDomain(\Biapy\CyrusBundle\Entity\Domain $grantedDomains)
    {
        $this->grantedDomains->removeElement($grantedDomains);
    }

    /**
     * Get grantedDomains
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGrantedDomains()
    {
        return $this->grantedDomains;
    }
    
    public function addGrantedDomains($grantedDomain){
    	if(!$this->grantedDomains->contains($grantedDomain)){
    		$this->grantedDomains->add($grantedDomain);
    	}
    }
    
    public function getGrantedDomainsList(){
    	return $this->grantedDomains;
    }
    
    /**
     * Generate Recovery Token 
     *
     * @return User
     */
    public function generateRecoveryToken()
    {
    	$defaultEncoder = new MessageDigestPasswordEncoder('sha256', false, 5000);
    	$generator = new SecureRandom();
    	 
    	//Generate random data to encode
    	$dataToEncore = $generator->nextBytes(10);
    	 
    	//Generate salt: A salt can't contain '{' or '}'.
    	$salt = $generator->nextBytes(10);
    	 
    	while(false !== strrpos($salt, '{') || false !== strrpos($salt, '}'))
    	{
    		//$generator = new SecureRandom();
    		$salt = $generator->nextBytes(10);
    	}
    	
    	$this->recovery_token = $defaultEncoder->encodePassword($dataToEncore, $salt);
    	return $this;
    }
    
    /**
     * Get recovery token
     *
     * @return string
     */
    public function getRecoveryToken()
    {
    	return $this->recovery_token;
    }
    
    /**
     * Clear recovery token
     *
     * @return User
     */
    public function clearRecoveryToken()
    {
    	$this->recovery_token = null;
    	return $this;
    }
    
    /**
     * Set recoveryExpiry
     *
     * @param DateTime $expiry
     * @return User
     */
    public function setRecoveryExpiry($expiry)
    {
    	$this->recovery_expiry = $expiry;
    	return $this;
    }
    
    /**
     * Get recoveryExpiry
     *
     * @return recovery_expiry
     */
    public function getRecoveryExpiry()
    {
    	return $this->recovery_expiry;
    }
    
    
    
    /*
     * Methods for the advancedUserInterface
     */
    public function isAccountNonExpired()
    {
    	return true;
    }
    
    public function isAccountNonLocked()
    {
    	return true;
    }
    
    public function isCredentialsNonExpired()
    {
    	return true;
    }
    
    public function isEnabled()
    {
    	return $this->getEnabled();
    }
    

    /**
     * Set recovery_token
     *
     * @param string $recoveryToken
     * @return User
     */
    public function setRecoveryToken($recoveryToken)
    {
        $this->recovery_token = $recoveryToken;

        return $this;
    }
}
