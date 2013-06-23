<?php

namespace Biapy\CyrusBundle\Extension;
 
use Sonata\AdminBundle\Admin\Admin;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

abstract class ExtendedAdmin extends Admin
{
	
	/**
     * Container
     * @var \Symfony\Component\DependencyInjection\ContainerInterface

     */
    protected $serviceContainer;
    
    /**
     * Security Context
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    protected $securityContext;
    
    public function setServiceContainer(ContainerInterface $serviceContainer)
    {
    	$this->serviceContainer = $serviceContainer;
    	$this->setSecurityContext($this->serviceContainer->get('security.context'));
    }
    
    
    public function getServiceContainer()
    {
    	return $this->$serviceContainer;
    }
    

    public function setSecurityContext(SecurityContextInterface $securityContext)
    {
    	$this->securityContext = $securityContext;
    }
    
    
    public function getSecurityContext()
    {
    	return $this->securityContext;
    }
}

?>