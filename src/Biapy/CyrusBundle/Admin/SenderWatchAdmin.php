<?php

namespace Biapy\CyrusBundle\Admin;
 
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Biapy\CyrusBundle\Entity\SenderWatch;

class SenderWatchAdmin extends Admin
{
    // setup the defaut sort column and order
    protected $datagridValues = array(
        '_sort_order' => 'ASC',
        '_sort_by' => 'domain',
    );
 
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('sender_address')
            ->add('target')
            ->add('enabled')
        ;
    }
 
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('sender_address')
            ->add('target')
            ->add('enabled')
        ;
    }
 
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('sender_address')
            ->add('target')
            ->add('enabled')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'view' => array(),
                    'edit' => array(),
                    'delete' => array(),
                )
            ))
        ;
    }
 
    protected function configureShowField(ShowMapper $showMapper)
    {
        $showMapper
            ->add('sender_address')
            ->add('target')
            ->add('enabled')
        ;
    }
    
    public function validate(ErrorElement $errorElement, $object){
    	if($object->getEnabled()){
    
    		/* 
    		 * sender_address 	- Email	- Max Length: 85 characters	- Can't be empty
    		 * target 			- Email - Max Length: 85 characters	- Can't be empty
    		 * 
    		 * Max length: 255 bytes max. In UTF8: 1 char = 3 bytes, so we have a maximum of 85 characters.
    		 */
    
    		$maxLengthSender = 85;
    		$maxLengthTarget = 85;
    
    		$errorElement	->with('sender_address')
    							->assertNotBlank()
    							->assertEmail()
    							->assertMaxLength(array('limit' => $maxLengthSender))
		    					->end()
    						->with('target')
    							->assertNotBlank()
    							->assertMaxLength(array('limit' => $maxLengthTarget))
    						->end();
    	}
    }

}

