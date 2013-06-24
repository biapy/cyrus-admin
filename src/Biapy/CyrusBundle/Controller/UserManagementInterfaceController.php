<?php

namespace Biapy\CyrusBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Biapy\CyrusBundle\Entity\User;
use Biapy\CyrusBundle\Form\UserType;

class UserManagementInterfaceController extends Controller
{
    public function indexAction()
    {
   		$name = $this->get('security.context')->getToken()->getUser()->getUsername();
        return $this->render('BiapyCyrusBundle:Default:index.html.twig', array('name' => $name));
    }
    
    public function recoveryAction()
    {
    	return $this->render('BiapyCyrusBundle:Default:recovery.html.twig');
    	 
    }
    
    public function recoveryEmailAction()
    {
    	$user = new User();
    	$form = $this	->createFormBuilder($user)
    					->add('username', 'text')
    					->getForm();

    	$request = $this->getRequest();
    	
    	if($this->getRequest()->isMethod('POST'))
    	{
    		$entityManager = $this->getDoctrine()->getManager();
    		$form->bind($request);
    		$user = $form->getData();
    		
    		//input analysis
    		$exploded = explode('@', $user->getUsername());
    		if(sizeof($exploded) != 2)
    		{
    			return $this->render('BiapyCyrusBundle:Default:recoveryEmail.html.twig', array('form' => $form->createView(), 'flash' => 'This user doesn\'t exist'));
    		}
    		
    		//Doctrine query
    		$repository = $this->getDoctrine()->getRepository('BiapyCyrusBundle:User');
    		$query = $repository	->createQueryBuilder('u')
    								->select('u')->from('Biapy\CyrusBundle\Entity\User', 'q')
    								->innerJoin('u.domain', 'd')
    								->where('u.username = :username AND d.name = :domain')
    								->setParameter('username', $exploded[0])
    								->setParameter('domain', $exploded[1])
    								->getQuery();
    		
    		$users = $query->getResult();
    		
    		if($users != null && sizeof($users) == 1 && $users[0]->getDomain()->getName() == $exploded[1])
    		{
    			
    			//TODO: Improve checks such as looking if the user actually as a recovery email. Also, implement the part to send to all the domain admin
    			//Recovery granted
    			$user = $users[0];
    			$date = new \DateTime();
    			$user->generateRecoveryToken();
    			$user->setRecoveryExpiry($date);
    			
    			$entityManager->flush($user);
    			
    			//DEBUG: Uncomment when ready
    			
    			//Send a mail to the user
    			/*$message = \Swift_Message::newInstance()
    			 			->setSubject('Test swiftMail')
    			 			->setContentType("text/html")
    						//->setFrom('TOBESET')
    						//->setTo('TOBESET')
    						->setBody(
    										$this->renderView('BiapyCyrusBundle:Default:mailRecoveryTemplate.html.twig', 
    										array(	'toUser' 	=> true,
    												'toAdmins' 	=> false, 
    												'user' 		=> $user->getUsername(), 
    												'date' 		=> $date,  
    												'token' 	=> $user->getRecoveryToken())
    										

    								)
    								
    			);
    			$this->get('mailer')->send($message);
    			*/
    			
    			//DEBUG: To be removed
    			return $this->render('BiapyCyrusBundle:Default:mailRecoveryTemplate.html.twig', array('toUser' => true, 'toAdmins' => false, 'user' => $user->getUsername(), 'date' => $date,  'token' => $user->getRecoveryToken()));
    		}
    		
    		return $this->render('BiapyCyrusBundle:Default:recoveryEmail.html.twig', array('form' => $form->createView(), 'flash' => 'This user doesn\'t exist'));
    		
    	}
    	    	
    	return $this->render('BiapyCyrusBundle:Default:recoveryEmail.html.twig', array('form' => $form->createView(), 'flash' => ''));
    	
    }
    
    public function recoveryAdminAction()
    {
    	return $this->render('BiapyCyrusBundle:Default:recovery.html.twig');
    
    }
    
    public function editAction()
    {
    	
    	$entityManager = $this->getDoctrine()->getEntityManager();
    	$form = $this->createForm(new UserType(), $this->get('security.context')->getToken()->getUser());
    	$request = $this->getRequest();
    	
    	if($request->isMethod('POST'))
    	{
            $form->bindRequest($request); /* Look in the request everything that can be sueful for the form */
            
            $user = $form->getData();
            $entityManager->persist($user);
            $entityManager->flush();
            
            return $this->redirect($this->generateUrl('user_interface_homepage'));
        }
        
    	return $this->render('BiapyCyrusBundle:Default:editUser.html.twig', array('form' => $form->createView()));
    }
    
    public function recoveryTokenAction($token){
    	if($token == null){
    		return $this->render('BiapyCyrusBundle:Default:recovery.html.twig');
    	}
    	
    	$user = new User();
    	$form = $this	->createFormBuilder($user)
    					->add('username', 'text')
    					->getForm();
    	$entityManager = $this->getDoctrine()->getManager();
    	$user = $entityManager->getRepository('BiapyCyrusBundle:User')->findOneBy(array('recovery_token' => $token));

				
		if($user != null){
			//TODO: When debug over, uncomment next line
			//$user->clearRecoveryToken();
			$expirtyDate = $user->getRecoveryExpiry();
			$currentDate = new \DateTime();
			
			//Look if the recovery is no more than 24h old:
			if( $currentDate->diff($expirtyDate)->format("%H") > 24){
				
				return $this->render('BiapyCyrusBundle:Default:recoveryEmail.html.twig', array('form' => $form->createView(), 'flash' => 'Token too old!'));
				
			}
			
			//TODO: Recovery form
			return $this->render('BiapyCyrusBundle:Default:recoveryEmail.html.twig', array('form' => $form->createView(), 'flash' => 'TODO: Recovery form'));
			
			
			   
		}
				
		return $this->render('BiapyCyrusBundle:Default:recoveryEmail.html.twig', array('form' => $form->createView(), 'flash' => 'Token not found!'));
				
    }
}
