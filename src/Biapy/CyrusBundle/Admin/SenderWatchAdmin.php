<?php

namespace Biapy\CyrusBundle\Admin;
 
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Biapy\CyrusBundle\Entity\SenderWatch;
use Biapy\CyrusBundle\Extension\ExtendedAdmin;

class SenderWatchAdmin extends ExtendedAdmin
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
    		 * sender_address 	- Needs to fit email pattern	- Max Length: 255 characters	- Can't be empty
    		 * target 			- Needs to fit email pattern	- Max Length: 255 characters	- Can't be empty
    		 */
    
    		$maxLengthSender = 255;
    		$maxLengthTarget = 255;
    
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

