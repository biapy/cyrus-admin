<?php

namespace Biapy\CyrusBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Biapy\CyrusBundle\Entity\User;
use Biapy\CyrusBundle\Form\UserType;

class UserManagementInterfaceController extends Controller
{

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

    	$defaultRecoveryEmailOptions = array('form' => $form->createView(), 'flash' => $this->get('translator')->trans("This user doesn't exist"), 'message' => $this->get('translator')->trans('Recovery by mail'), 'validToken' => false);

    	if($this->getRequest()->isMethod('POST'))
    	{
    		$entityManager = $this->getDoctrine()->getManager();
    		$form->bind($request);
            $data = $form->getData();
    		$username = $form->getData()->getUsername();

    		//Get the user that want to be notified.
    		$repository = $this->getDoctrine()->getRepository('BiapyCyrusBundle:User');
    		$user = $repository->loadUserByUsername($username);

    		if($user)
    		{

    			//Recovery granted
    			if($user->getRecoveryMail() != "")
                {
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


    				//Send a mail to the user
    				$message = \Swift_Message::newInstance()
    				 				->setSubject('Password Recovery')
    								->setContentType("text/html")
    								->setFrom($user->getEmail())
    								->setTo($user->getRecoveryMail())
				    				->setBody(
				    						$this->renderView('BiapyCyrusBundle:Default:mailRecoveryTemplate.html.twig',
				    								array(	'toUser' 	=> true,
				    										'user' 		=> $user->getEmail(),
				    										'date' 		=> $date,
				    										'token' 	=> $user->getRecoveryToken(),
				    										'baseurl'	=> $this->getRequest()->getBaseURL(),
    														)
				    						)
    				);
    				$this->get('mailer')->send($message);


    				//This first line shows the recovery mail that is sent to the user recovery mail.
    				//return $this->render('BiapyCyrusBundle:Default:mailRecoveryTemplate.html.twig', array('toUser' => true, 'user' => $user->getUsername(), 'date' => $date,  'token' => $user->getRecoveryToken(), 'baseurl'	=> $this->getRequest()->getHost()));
    				//This second line just show what the user that want its password recovered has to see in production.
    				return $this->render('BiapyCyrusBundle:Default:recoveryEmail.html.twig', array('message' => $this->get('translator')->trans("A mail has been sent to your recovery mail")));
    			}
                else
                {
    				return $this->render('BiapyCyrusBundle:Default:recoveryEmail.html.twig', array('flash' => "This user doesn't have any recovery mail.."));
                }

    		}

    		return $this->render('BiapyCyrusBundle:Default:recoveryEmail.html.twig', $defaultRecoveryEmailOptions);
    	}

    	return $this->render('BiapyCyrusBundle:Default:recoveryEmail.html.twig', array('form' => $form->createView(), 'message' => $this->get('translator')->trans("Please insert the username of the account to recover"), 'validToken' => false));
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

            $username = $form->getData()['username'];
            $addedMessage = $form->getData()['message'];

            $repository = $this->getDoctrine()->getRepository('Biapy\CyrusBundle\Entity\User');
            $user = $repository->loadUserByUsername($username);

            if($user)
            {
                //Get the list of enabled admins.
                $admins = $repository->createQueryBuilder('admin')
                        ->leftJoin('admin.grantedDomains', 'domain')
                        ->leftJoin('domain.users', 'user')
                        ->where('admin.enabled = :enabled')
                        ->andWhere('admin.has_mailbox = :has_mailbox')
                        ->andWhere('admin.is_super_admin = :is_super_admin OR user = :user')
                        ->setParameter('is_super_admin', true)
                        ->setParameter('user', $user)
                        ->setParameter('enabled', true)
                        ->setParameter('has_mailbox', true)
                        ->getQuery()
                        ->getResult();

    			$date = new \DateTime();

    				/*
    				*
    				*  To be uncommented for the production:
    				*  This is really going to send the mail to the recovery user mail,
    				*  											 each super admin mail,
    				*  											 each domain admin mail
    				*/

    				//Send a mail to every admin address:
                    $adminEmails = array_map(function($user) { return $user->getEmail(); }, $admins);

    				$message = \Swift_Message::newInstance()
				    				->setSubject('A user asked for a password recovery link to its mailbox')
				    				->setContentType("text/html")
				    				->setFrom($user->getEmail())
				    				->setTo($adminEmails)
				    				->setBody(
				    						$this->renderView('BiapyCyrusBundle:Default:mailRecoveryTemplate.html.twig',
				    								array(	'toAdmins' 	=> true,
				    										'user' 		=> $user->getEmail(),
				    										'date' 		=> $date,
				    										'baseurl'	=> $this->getRequest()->getBaseURL(),
				    										'added_message' => $addedMessage,
				    								)
				    						)
    				                );
    				                $this->get('mailer')->send($message);


    				//This second line just show what the user that want its password recovered has to see in production.
    				return $this->render('BiapyCyrusBundle:Default:recoveryEmail.html.twig', array('message' => $this->get('translator')->trans("A mail has been sent to the administrators")));
    		}

    		return $this->render('BiapyCyrusBundle:Default:recoveryAdmin.html.twig', array('form' => $form->createView(), 'message' => $this->get('translator')->trans("This user doesn't exist")));
    	}

    	return $this->render('BiapyCyrusBundle:Default:recoveryAdmin.html.twig', array('form' => $form->createView(), 'message' => $this->get('translator')->trans("Please insert your username and a message for the administrators")));
    }

    public function editAction()
    {
    	$entityManager = $this->getDoctrine()->getManager();
    	$form = $this	->createFormBuilder(array())
    					->add('password', 'password', array('required' => false))
    					->add('new_password', 'password', array('required' => false, 'label' => $this->get('translator')->trans("New Password")))
    					->add('new_password_again', 'password', array('required' => false, 'label' => $this->get('translator')->trans("Password Confirmation")))
    					->add('recovery_email', 'email', array('data' => $this->getUser()->getRecoveryMail(), 'required' => false, 'label' => $this->get('translator')->trans("Recovery Mail")))
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
    			if( '' != $data['new_password'] && '' != $data['password'] && $user->getPassword() == $data['password'] )
                {
    				if($data['new_password'] == $data['new_password_again'])
                    {

    					$user->setPassword($data['new_password']);
    					$entityManager->persist($user);
    					$entityManager->flush();
	    				$message .= $this->get('translator')->trans("Password correctly changed");
    				}
                    else
                    {
    					$message .= $this->get('translator')->trans("The two fields for the new password do not match");
                    }
    			}
                else
                {
    				$message .= $this->get('translator')->trans("Wrong password");
                }

    		}

    		//Recovery email
    		if($data['recovery_email'] != $this->getUser()->getRecoveryMail()){
    			if('' != $data['password'] && $user->getPassword() == $data['password']){

    				$user->setRecoveryMail($data['recovery_email']);
    				$entityManager->persist($user);
    				$entityManager->flush();
    				$message .= $this->get('translator')->trans("Recovery mail correctly changed");
    			} else
    				$message .= $this->get('translator')->trans("Wrong password");
    		}

    		return $this->render('BiapyCyrusBundle:Default:editUser.html.twig', array('form' => $form->createView(), 'message' => $message));
    	}
    	return $this->render('BiapyCyrusBundle:Default:editUser.html.twig', array('form' => $form->createView(), 'message' => $this->get('translator')->trans("User Settings Panel")));
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
    		return $this->render('BiapyCyrusBundle:Default:recoveryEmail.html.twig', array('message' => $this->get('translator')->trans("Password correctly changed"), 'validToken' => true));
    	}

		if($user != null){
			$form = $this	->createFormBuilder($user)
							->add('username', 'text')
							->getForm();

			$expirtyDate = $user->getRecoveryExpiry();
			$currentDate = new \DateTime();

			//Look if the recovery is no more than 24h old:
			if( $currentDate->diff($expirtyDate)->format("%H") > 24){
				return $this->render('BiapyCyrusBundle:Default:recoveryEmail.html.twig', array('form' => $form->createView(), 'flash' => $this->get('translator')->trans("Token too old!"), 'message' => $this->get('translator')->trans("Recovery by mail").":", 'validToken' => false));
			}

			$user = new User();
			$formPassword = $this	->createFormBuilder($user)
									->add('password', 'password')
									->getForm();
			return $this->render('BiapyCyrusBundle:Default:recoveryEmail.html.twig', array('form' => $formPassword->createView(), 'message' => $this->get('translator')->trans("Please insert a new password").":", 'validToken' => true, 'token' => $token ));
		}

		//user is null
		$user = new User();
		$form = $this	->createFormBuilder($user)
						->add('username', 'text')
						->getForm();

		return $this->render('BiapyCyrusBundle:Default:recoveryEmail.html.twig', array('form' => $form->createView(), 'flash' => $this->get('translator')->trans("Token not found"), 'message' => $this->get('translator')->trans('Recovery by mail').":", 'validToken' => false));

    }


}
