<?php

namespace Biapy\CyrusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AliasTarget
 */
class AliasTarget
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $target;

    /**
     * @var \Biapy\CyrusBundle\Entity\Alias
     */
    private $alias;


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
     * String representation.
     */
    public function __toString()
    {
      return strval($this->getTarget());
    }

    /**
     * Set target
     *
     * @param string $target
     * @return AliasTarget
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
     * Set alias
     *
     * @param \Biapy\CyrusBundle\Entity\Alias $alias
     * @return AliasTarget
     */
    public function setAlias(\Biapy\CyrusBundle\Entity\Alias $alias = null)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Get alias
     *
     * @return \Biapy\CyrusBundle\Entity\Alias 
     */
    public function getAlias()
    {
        return $this->alias;
    }
}
