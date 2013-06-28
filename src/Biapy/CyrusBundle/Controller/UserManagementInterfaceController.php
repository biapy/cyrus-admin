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
    	
    	$defaultRecoveryEmailOptions = array('form' => $form->createView(), 'flash' => 'This user doesn\'t exist', 'message' => 'Recovery by mail:', 'validToken' => false);
    	
    	if($this->getRequest()->isMethod('POST'))
    	{
    		$entityManager = $this->getDoctrine()->getManager();
    		$form->bind($request);
    		$user = $form->getData();
    		
    		//input analysis
    		$exploded = explode('@', $user->getUsername());
    		if(sizeof($exploded) != 2)
    		{
    			return $this->render('BiapyCyrusBundle:Default:recoveryEmail.html.twig', $defaultRecoveryEmailOptions);
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
    			
    			return $this->render('BiapyCyrusBundle:Default:mailRecoveryTemplate.html.twig', array('toUser' => true, 'toAdmins' => false, 'user' => $user->getUsername(), 'date' => $date,  'token' => $user->getRecoveryToken()));
    		}
    		
    		return $this->render('BiapyCyrusBundle:Default:recoveryEmail.html.twig', $defaultRecoveryEmailOptions);
    		
    	}
    	    	
    	return $this->render('BiapyCyrusBundle:Default:recoveryEmail.html.twig', array('form' => $form->createView(), 'flash' => false, 'message' => 'Please insert the username of the account to recover', 'validToken' => false));
    	
    }
    
    public function recoveryAdminAction()
    {
    	return $this->render('BiapyCyrusBundle:Default:recovery.html.twig');
    
    }
    
    public function editAction()
    {
    	
    	$entityManager = $this->getDoctrine()->getEntityManager();
    	$form = $this	->createFormBuilder(array())
    					->add('old_password', 'password', array('required' => false))
    					->add('new_password', 'password', array('required' => false))
    					->add('recovery_email', 'email', array('data' => $this->getUser()->getRecoveryMail(), 'required' => false))
    					->getForm();

    	$request = $this->getRequest();
    	if($request->isMethod('POST'))
    	{
    		$form->bindRequest($request);
    		$data = $form->getData();
    		$user = $this->getUser();
    		$message = "";
    		
    		//Password
    		if($data['old_password'] != '')
    		{
    			if( '' != $data['new_password'] && '' != $data['old_password'] && $user->getPassword() == $data['old_password'] ){
    				$user->setPassword($data['new_password']);
    				$entityManager->persist($user);
    				$entityManager->flush();
	    			$message .= "Password correctly changed!";
    			}else
    				$message .= "Wrong password";
    			
    		}
    		
    		//Recovery email
    		if($data['recovery_email'] != $this->getUser()->getRecoveryMail()){
    				$user->setRecoveryMail($data['recovery_email']);
    				$entityManager->persist($user);
    				$entityManager->flush();
    				$message .= "\nRecovery mail correctly changed!";
    		}
    		
    		return $this->render('BiapyCyrusBundle:Default:editUser.html.twig', array('form' => $form->createView(), 'message' => $message));
    	}
    	return $this->render('BiapyCyrusBundle:Default:editUser.html.twig', array('form' => $form->createView(), 'message' => 'User setting panel'));
    }
    
    public function recoveryTokenAction($token){
    	if($token == null){
    		return $this->render('BiapyCyrusBundle:Default:recovery.html.twig');
    	}
    	
    	$entityManager = $this->getDoctrine()->getManager();
    	$user = $entityManager->getRepository('BiapyCyrusBundle:User')->findOneBy(array('recovery_token' => $token));
    	
    	if($user != null && $this->getRequest()->isMethod('POST')){
    		$form = $this	->createFormBuilder($user)
    								->add('password', 'password')
    								->getForm();
    		$form->bindRequest($this->getRequest());
    		$user->setPassword($form->getData()->getPassword());
    		$user->clearRecoveryToken();
    		
    		$entityManager->persist($user);
    		$entityManager->flush();
    		return $this->render('BiapyCyrusBundle:Default:recoveryEmail.html.twig', array('form' => false, 'flash' => false, 'message' =>'Password successfully changed!'));
    	}
    	
		if($user != null){
			$form = $this	->createFormBuilder($user)
							->add('username', 'text')
							->getForm();
			
			$expirtyDate = $user->getRecoveryExpiry();
			$currentDate = new \DateTime();
			
			//Look if the recovery is no more than 24h old:
			if( $currentDate->diff($expirtyDate)->format("%H") > 24){
				return $this->render('BiapyCyrusBundle:Default:recoveryEmail.html.twig', array('form' => $form->createView(), 'flash' => 'Token too old!', 'message' => 'Recovery by mail:', 'validToken' => false));
			}
			
			$user = new User();
			$formPassword = $this	->createFormBuilder($user)
									->add('password', 'password')
									->getForm();
			return $this->render('BiapyCyrusBundle:Default:recoveryEmail.html.twig', array('form' => $formPassword->createView(), 'flash' => false, 'message' => 'Please insert a new password:', 'validToken' => true, 'token' => $token ));
		}

		//user is null
		$user = new User();
		$form = $this	->createFormBuilder($user)
						->add('username', 'text')
						->getForm();
		
		return $this->render('BiapyCyrusBundle:Default:recoveryEmail.html.twig', array('form' => $form->createView(), 'flash' => 'Token not found!', 'message' => 'Recovery by mail:', 'validToken' => false));
				
    }
}
