<?php

namespace Biapy\CyrusBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpKernel\HttpKernel;

class UnauthorizedAccessController extends Controller
{
    public function unauthorizedAction()
    {
    	if($this->get('security.context')->isGranted('ROLE_USER'))
    	{
			return $this->redirect($this->generateUrl('user_interface_homepage'));
    	} 
    	else 
    	{
    		return $this->redirect($this->generateUrl('login'));
    	}
    	
    }
}
