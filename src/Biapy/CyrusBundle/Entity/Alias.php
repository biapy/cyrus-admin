<?php

namespace Biapy\CyrusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Alias
 */
class Alias
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $aliasname;

    /**
     * @var boolean
     */
    private $enabled;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $alias_targets;

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
        $this->alias_targets = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set aliasname
     *
     * @param string $aliasname
     * @return Alias
     */
    public function setAliasname($aliasname)
    {
        $this->aliasname = $aliasname;

        return $this;
    }

    /**
     * Get aliasname
     *
     * @return string 
     */
    public function getAliasname()
    {
        return $this->aliasname;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return Alias
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
     * Add alias_targets
     *
     * @param \Biapy\CyrusBundle\Entity\AliasTarget $aliasTargets
     * @return Alias
     */
    public function addAliasTarget(\Biapy\CyrusBundle\Entity\AliasTarget $aliasTargets)
    {
        $this->alias_targets[] = $aliasTargets;

        return $this;
    }

    /**
     * Remove alias_targets
     *
     * @param \Biapy\CyrusBundle\Entity\AliasTarget $aliasTargets
     */
    public function removeAliasTarget(\Biapy\CyrusBundle\Entity\AliasTarget $aliasTargets)
    {
        $this->alias_targets->removeElement($aliasTargets);
    }

    /**
     * Get alias_targets
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAliasTargets()
    {
        return $this->alias_targets;
    }

    /**
     * Set domain
     *
     * @param \Biapy\CyrusBundle\Entity\Domain $domain
     * @return Alias
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

    public function getTargets()
    {
      return implode("\n", $this->getAliasTargets()->toArray());
    }

    public function setTargets($targets)
    {
      $targets_list = explode("\n", $targets);
      foreach($targets_list as $key => $value)
      {
        $targets_list[$key] = trim($value);
      }

      $targets_list = array_unique(array_filter($targets_list));
      $current_targets = $this->getAliasTargets()->toArray();

      foreach($current_targets as $target)
      {
        if(! in_array($target->getTarget(), $targets_list))
        {
          $this->removeAliasTarget($target);
        }
      }

      $current_targets = $this->getAliasTargets()->toArray();

      foreach($targets_list as $target)
      {
        if(! in_array($target, $current_targets))
        {
          $alias_target = new \Biapy\CyrusBundle\Entity\AliasTarget();
          $alias_target->setAlias($this);
          $alias_target->setTarget($target);
          $this->addAliasTarget($alias_target);
        }
      }
    }

    public function getEmail()
    {
        return strval(sprintf("%s@%s", $this->getAliasname(), $this->getDomain() ? $this->getDomain() : '*'));
    }

    /**
     * String representation.
     */
    public function __toString()
    {
        return $this->getEmail();
    }
}
