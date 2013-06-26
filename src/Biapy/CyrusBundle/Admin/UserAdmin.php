<?php

namespace Biapy\CyrusBundle\Admin;
 
use Symfony\Component\Security\Core\SecurityContextInterface;
use Sonata\AdminBundle\Route\RouteCollection;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Biapy\CyrusBundle\Extension\ExtendedAdmin;
use Biapy\CyrusBundle\Entity\User;

class UserAdmin extends ExtendedAdmin
{
    // setup the defaut sort column and order
    protected $datagridValues = array(
        '_sort_order' => 'ASC',
        '_sort_by' => 'domain',
    );

    protected function configureFormFields(FormMapper $formMapper)
    {   
    	//Determine highest role of the logged in user:
    	if( $this->getSecurityContext()->isGranted('ROLE_ADMIN_DOMAIN') )
    	{
    		
    		if( $this->securityContext->isGranted('ROLE_SUPER_ADMIN') )
    		{
    			//A super admin can see all the domains even if not all are granted
    			$formMapper	->add('domain');
    		} else 
    		{
    			$formMapper
    				->add('domain', null, array('choices' => $this->getSecurityContext()->getToken()->getUser()->getGrantedDomains()));
    		}
    		
    		$formMapper	->add('username')
    					->add('password')
    					->add('enabled', null, array('required' => false));
    		
    		if( $this->securityContext->isGranted('ROLE_SUPER_ADMIN') )
    		{
    			
    			$formMapper	->add('grantedDomains', null, array('required' => false))
    						->add('is_super_admin', null, array('required' => false));
    		} else
    		{
    			$formMapper->add('grantedDomains', null, array('required' => false, 'choices' => $this->getSecurityContext()->getToken()->getUser()->getGrantedDomains()));
    		}
    	}
        
    }
 
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('domain')
            ->add('username')
            ->add('enabled')
        ;
        
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('username')
            ->add('domain')
            ->add('enabled')
            ->add('_action', 'actions', array(
                	'actions'	=> array(
                    'view' 		=> array(),
                    'edit' 		=> array(),
                    'delete' 	=> array(),
                )
            ))
        ;
    }
    
    protected function configureShowField(ShowMapper $showMapper)
    {    	
        $showMapper
            ->add('domain')
            ->add('username')
            ->add('password')
            ->add('enabled')
        ;
    }
    
    public function validate(ErrorElement $errorElement, $object){
    	if($object->getEnabled()){
    		
    		/* 
    		 * username	- Needs to fit username pattern	- Max Length: 255 characetrs 	- Can't be empty
    		 * password 								- Max Length: 255 characters	- Can't be empty
    		 */
    		
    		$maxLengthUsername = 255;
    		$maxLengthPassword = 255;
    		$regexUsername = '/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*$/iD';

    		
    		$errorElement	->with('username')
    							->assertNotNull()
    							->assertNotBlank()
    							->assertLength(array('max' => $maxLengthUsername))
    							->assertRegex(array('pattern' => $regexUsername))
    							->end()
    						->with('domain')
    							->assertNotNull()
    							->assertNotBlank()
    							->end()
    						->with('password')
    							->assertNotBlank()
    							->assertLength(array('max' => $maxLengthPassword))
    							->end();
    	}
    }
    
    public function createQuery($context = 'list')
    {    	
    	//Unless the user is s super admin, we want to show only to the super administrator the user that belong to its domain.
    	$query = parent::createQuery($context);
		$securityContext = $this->getSecurityContext();
		$user = $securityContext->getToken()->getUser();
		$domains = $user->getGrantedDomains();
    	if( !$securityContext->isGranted('ROLE_SUPER_ADMIN')){

			foreach( $domains as $domain){
				$query->orWhere($query->expr()->eq('o.domain', $domain->getId()));
			}
					
    	}
    	return $query;
    }

    

}

