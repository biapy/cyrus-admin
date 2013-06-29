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
    					->add('username', 'email')
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
    		
    		//Get the user that want to be notified.
    		$repository = $this->getDoctrine()->getRepository('BiapyCyrusBundle:User');
    		$query = $repository	->createQueryBuilder('u')
    								->select('u')->from('Biapy\CyrusBundle\Entity\User', 'q')
    								->innerJoin('u.domain', 'd')
    								->where('u.username = :username AND d.name = :domain AND u.enabled = :enabled')
    								->setParameter('username', $exploded[0])
    								->setParameter('domain', $exploded[1])
    								->setParameter('enabled', '1')
    								->getQuery();
    		$users = $query->getResult();
    		//Get the list of the super admins that are unabled.
    		$query = $repository	->createQueryBuilder('u')
    								->select('u')->from('Biapy\CyrusBundle\Entity\User', 'q')
    								->where('u.is_super_admin = :is_super_admin AND u.enabled = :enabled')
    								->setParameter('is_super_admin', '1')
    								->setParameter('enabled', '1')
    								->getQuery();
    		$superAdmins = $query->getResult();
    		
    		//Get the list of domain admins of this domain that are enabled 
    		$query = $repository	->createQueryBuilder('q')
    								->select('u')->from('Biapy\CyrusBundle\Entity\User', 'u')
    								->innerJoin('u.grantedDomains', 'g', 'WITH', 'g.name = :domain')
    								->where('u.enabled =:enabled')
    								->setParameter('domain', $exploded[1])
    								->setParameter('enabled', '1')
    								->getQuery();
    		$domainAdmin = $query->getResult();
    		    		
    		if($users != null && sizeof($users) == 1 && $users[0]->getDomain()->getName() == $exploded[1])
    		{
    			
    			//Recovery granted
    			$user = $users[0];
    			if($user->getRecoveryMail() != ""){
    				
    				$date = new \DateTime();
    				$user->generateRecoveryToken();
    				$user->setRecoveryExpiry($date);
    				 
    				$entityManager->flush($user);
    				 
    				
    				/*
    				 * 
    				 *  To be uncommented for the production:
    				 *  This is really going to send the mail to the recovery user mail,
    				 *  											 each super admin mail,
    				 *  											 each domain admin mail
    				 */
    				
    				/*
    				//Send a mail to the user
    				$message = \Swift_Message::newInstance()
    				 				->setSubject('Password Recovery')
    								->setContentType("text/html")
    								->setFrom("donotanswer@cyrusbundle.com")
    								->setTo($user->getRecoveryMail())
				    				->setBody(
				    						$this->renderView('BiapyCyrusBundle:Default:mailRecoveryTemplate.html.twig',
				    								array(	'toUser' 	=> true,
				    										'user' 		=> $user->getUsername()."@".$user->getDomain()->getName(),
				    										'date' 		=> $date,
				    										'token' 	=> $user->getRecoveryToken(),
				    										'baseurl'	=> $this->getRequest()->getHost(),			    										
    														)
				    										
				    				
				    				
				    						)
    				
    				);
    				$this->get('mailer')->send($message);
    				
    				//Send a mail to every admin address:
    				foreach( $superAdmins as $admin)
    				{
    					//Send a mail to the super admin
    					$message = \Swift_Message::newInstance()
				    					->setSubject('A user asked for a password reovery link to its mailbox')
				    					->setContentType("text/html")
				    					->setFrom("donotanswer@cyrusbundle.com")
				    					->setTo($admin->getUsername()."@".$admin->getDomain()->getName())
				    					->setBody(
				    							$this->renderView('BiapyCyrusBundle:Default:mailRecoveryTemplate.html.twig',
				    									array(	'toAdmins' 	=> true,
				    											'user' 		=> $user->getUsername(),
				    											'date' 		=> $date,
				    											'token' 	=> $user->getRecoveryToken(),
				    											'baseurl'	=> $this->getRequest()->getHost(),
				    									)
				    					
				    					
				    					
				    							)
				    					
				    					);
    					$this->get('mailer')->send($message);
    				}
    				
    				//Send a mail to every domain admin of the admin the user belongs to
    				foreach( $domainAdmin as $admin)
    				{
    					//Send a mail to the super admin
    					$message = \Swift_Message::newInstance()
				    					->setSubject('A user asked for a password reovery link to its mailbox')
				    					->setContentType("text/html")
				    					->setFrom("donotanswer@cyrusbundle.com")
				    					->setTo($admin->getUsername()."@".$admin->getDomain()->getName())
				    					->setBody(
				    							$this->renderView('BiapyCyrusBundle:Default:mailRecoveryTemplate.html.twig',
				    									array(	'toAdmins' 	=> true,
				    											'user' 		=> $user->getUsername(),
				    											'date' 		=> $date,
				    											'token' 	=> $user->getRecoveryToken(),
				    											'baseurl'	=> $this->getRequest()->getHost(),
				    									)
				    										
				    										
				    										
				    							)
				    								
				    					);
    					$this->get('mailer')->send($message);
    				}*/
    				
    				//This first line shows the recovery mail that is sent to the user recovery mail.
    				return $this->render('BiapyCyrusBundle:Default:mailRecoveryTemplate.html.twig', array('toUser' => true, 'user' => $user->getUsername(), 'date' => $date,  'token' => $user->getRecoveryToken(), 'baseurl'	=> $this->getRequest()->getHost()));
    				//This second line just show what the user that want its password recovered has to see in production.
    				//return $this->render('BiapyCyrusBundle:Default:recoveryEmail.html.twig', array('message' => "A mail has been sent to your recovery mail."));
    			} else
    				return $this->render('BiapyCyrusBundle:Default:recoveryEmail.html.twig', array('flash' => "This user doesn't have any recovery mail..."));
    			
    		}
    		
    		return $this->render('BiapyCyrusBundle:Default:recoveryEmail.html.twig', $defaultRecoveryEmailOptions);
    		
    	}
    	    	
    	return $this->render('BiapyCyrusBundle:Default:recoveryEmail.html.twig', array('form' => $form->createView(), 'message' => 'Please insert the username of the account to recover', 'validToken' => false));
    	
    }
    
    public function recoveryAdminAction()
    {
    	$entityManager = $this->getDoctrine()->getManager();
    	$request = $this->getRequest();
    	$form = $this	->createFormBuilder(array())
    					->add('username', 'email', array('required' => true))
    					->add('message', 'textarea', array('required' => false, 'max_length' => 255))
    					->getForm();
    	
    	
    	if($request->isMethod('POST'))
    	{
    		$form->bindRequest($request);
    		
    		//input analysis
    		$exploded = explode('@', $form->getData()['username']);    		
    		if(sizeof($exploded) != 2)
    		{
    			
    			return $this->render('BiapyCyrusBundle:Default:recoveryAdmin.html.twig', array('form' => $form->createView(), 'message' => 'This user doesn\'t exist'));
    		}
    		
    		//Get the user that want to be notified.
    		$repository = $this->getDoctrine()->getRepository('BiapyCyrusBundle:User');
    		$query = $repository	->createQueryBuilder('u')
						    		->select('u')->from('Biapy\CyrusBundle\Entity\User', 'q')
						    		->innerJoin('u.domain', 'd')
						    		->where('u.username = :username AND d.name = :domain AND u.enabled = :enabled')
						    		->setParameter('username', $exploded[0])
						    		->setParameter('domain', $exploded[1])
						    		->setParameter('enabled', '1')
						    		->getQuery();
    		$users = $query->getResult();

    		//Get the list of the super admins that are unabled.
    		$query = $repository	->createQueryBuilder('u')
						    		->select('u')->from('Biapy\CyrusBundle\Entity\User', 'q')
						    		->where('u.is_super_admin = :is_super_admin AND u.enabled = :enabled')
						    		->setParameter('is_super_admin', '1')
						    		->setParameter('enabled', '1')
						    		->getQuery();
    		$superAdmins = $query->getResult();
    		
    		//Get the list of domain admins of this domain that are enabled
    		$query = $repository	->createQueryBuilder('q')
						    		->select('u')->from('Biapy\CyrusBundle\Entity\User', 'u')
						    		->innerJoin('u.grantedDomains', 'g', 'WITH', 'g.name = :domain')
						    		->where('u.enabled =:enabled')
						    		->setParameter('domain', $exploded[1])
						    		->setParameter('enabled', '1')
						    		->getQuery();
    		$domainAdmin = $query->getResult();
    		
    		if($users != null && sizeof($users) == 1 && $users[0]->getDomain()->getName() == $exploded[1])
    		{
    			 
    			//Recovery granted
    			$user = $users[0];
    			$date = new \DateTime();
    			$addedMessage = $form->getData()['message'];
    		
    				/*
    				*
    				*  To be uncommented for the production:
    				*  This is really going to send the mail to the recovery user mail,
    				*  											 each super admin mail,
    				*  											 each domain admin mail
    				*/
    		
    				/*
    				//Send a mail to every admin address:
    				foreach( $superAdmins as $admin)
    				{
    				//Send a mail to the super admin
    				$message = \Swift_Message::newInstance()
				    				->setSubject('A user asked for a password reovery link to its mailbox')
				    				->setContentType("text/html")
				    				->setFrom("donotanswer@cyrusbundle.com")
				    				->setTo($admin->getUsername()."@".$admin->getDomain()->getName())
				    				->setBody(
				    						$this->renderView('BiapyCyrusBundle:Default:mailRecoveryTemplate.html.twig',
				    								array(	'toAdmins' 	=> true,
				    										'user' 		=> $user->getUsername(),
				    										'date' 		=> $date,
				    										'baseurl'	=> $this->getRequest()->getHost(),
				    										'added_message' => $addedMessage,
				    								)
				    						   
				    						   
				    						   
				    						)
    						 
    				);
    				$this->get('mailer')->send($message);
    				}
    		
    				//Send a mail to every domain admin of the admin the user belongs to
    				foreach( $domainAdmin as $admin)
    				{
    				//Send a mail to the super admin
    				$message = \Swift_Message::newInstance()
				    				->setSubject('A user asked for a password reovery link to its mailbox')
				    				->setContentType("text/html")
				    				->setFrom("donotanswer@cyrusbundle.com")
				    				->setTo($admin->getUsername()."@".$admin->getDomain()->getName())
				    				->setBody(
				    						$this->renderView('BiapyCyrusBundle:Default:mailRecoveryTemplate.html.twig',
				    								array(	'toAdmins' 	=> true,
				    										'user' 		=> $user->getUsername(),
				    										'date' 		=> $date,
				    										'baseurl'	=> $this->getRequest()->getHost(),
				    										'added_message' => $addedMessage,
				    								)
				    		
				    		
				    		
				    						)
    		
    				);
    				$this->get('mailer')->send($message);
    				}*/
    		
    				//This first line shows the recovery mail that is sent to the admin.
    				return $this->render('BiapyCyrusBundle:Default:mailRecoveryTemplate.html.twig',
				    								array(	'toAdmins' 	=> true,
				    										'user' 		=> $user->getUsername(),
				    										'date' 		=> $date,
				    										'baseurl'	=> $this->getRequest()->getHost(),
				    										'added_message' => $addedMessage,
				    								)
				    		
				    		
				    		
				    						);
    				//This second line just show what the user that want its password recovered has to see in production.
    				//return $this->render('BiapyCyrusBundle:Default:recoveryEmail.html.twig', array('message' => "A mail has been sent to the admins."));
    		}
    		
    		return $this->render('BiapyCyrusBundle:Default:recoveryAdmin.html.twig', array('form' => $form->createView(), 'message' => 'This user doesn\'t exist'));
    	}
    	
    	return $this->render('BiapyCyrusBundle:Default:recoveryAdmin.html.twig', array('form' => $form->createView(), 'message' => 'Please insert your username and a message for the admins'));    
    }
    
    public function editAction()
    {
    	
    	$entityManager = $this->getDoctrine()->getManager();
    	$form = $this	->createFormBuilder(array())
    					->add('password', 'password', array('required' => false))
    					->add('new_password', 'password', array('required' => false))
    					->add('new_password_again', 'password', array('required' => false))
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
    		if($data['new_password'] != '')
    		{
    			if( '' != $data['new_password'] && '' != $data['password'] && $user->getPassword() == $data['password'] ){
    				if($data['new_password'] == $data['new_password_again']){
    					
    					$user->setPassword($data['new_password']);
    					$entityManager->persist($user);
    					$entityManager->flush();
	    				$message .= "Password correctly changed!";
    				} else
    					$message .= "The two fields for the new password do not match.";
    			}else
    				$message .= "Wrong password";
    			
    		}
    		
    		//Recovery email
    		if($data['recovery_email'] != $this->getUser()->getRecoveryMail()){
    			if('' != $data['password'] && $user->getPassword() == $data['password']){
    				
    				$user->setRecoveryMail($data['recovery_email']);
    				$entityManager->persist($user);
    				$entityManager->flush();
    				$message .= "\nRecovery mail correctly changed!";
    			} else
    				$message .= "Wrong password";
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
    		return $this->render('BiapyCyrusBundle:Default:recoveryEmail.html.twig', array('message' =>'Password successfully changed!', 'validToken' => true));
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
			return $this->render('BiapyCyrusBundle:Default:recoveryEmail.html.twig', array('form' => $formPassword->createView(), 'message' => 'Please insert a new password:', 'validToken' => true, 'token' => $token ));
		}

		//user is null
		$user = new User();
		$form = $this	->createFormBuilder($user)
						->add('username', 'text')
						->getForm();
		
		return $this->render('BiapyCyrusBundle:Default:recoveryEmail.html.twig', array('form' => $form->createView(), 'flash' => 'Token not found!', 'message' => 'Recovery by mail:', 'validToken' => false));
				
    }


}
