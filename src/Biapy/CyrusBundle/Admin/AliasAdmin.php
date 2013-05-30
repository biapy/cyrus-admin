<?php

namespace Biapy\CyrusBundle\Admin;
 
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Biapy\CyrusBundle\Entity\Alias;

class AliasAdmin extends Admin
{
    // setup the defaut sort column and order
    protected $datagridValues = array(
        '_sort_order' => 'ASC',
        '_sort_by' => 'domain',
    );
 
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('domain')
            ->add('aliasname')
            ->add('targets', 'textarea')
            ->add('enabled')
        ;
    }
 
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('domain')
            ->add('aliasname')
            ->add('enabled')
        ;
    }
 
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('aliasname')
            ->add('domain')
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
            ->add('domain')
            ->add('aliasname')
            ->add('enabled')
        ;
    }
    
    public function validate(ErrorElement $errorElement, $object){
    	if($object->getEnabled()){
    
    		/* 
    		 * aliasname	- Need to fit alias pattern		- Max Length: 85 characters	- Can't be empty
    		 * domain 		- Need to fit domain pattern	- Max Length: 85 characters - Can't be empty
    		 * 
    		 * Max length: 255 bytes max. In UTF8: 1 char = 3 bytes, so we have a maximum of 85 characters.
    		 */

    		$maxLengthAliasname = 85;
    		$regexAlias = '/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*$/iD';
    		    
    		$errorElement	->with('domain')
    							->assertNotBlank()
    							->end()
    						->with('aliasname')
    							->assertNotBlank()
    							->assertMaxLength(array('limit' => $maxLengthAliasname))
    							->assertRegex(array('pattern' => $regexAlias))
    						->end();
    	}
    }

}

