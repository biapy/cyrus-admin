<?php

namespace Biapy\CyrusBundle\Admin;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Biapy\CyrusBundle\Entity\Alias;
use Biapy\CyrusBundle\Extension\ExtendedAdmin;

class AliasAdmin extends ExtendedAdmin {
	// setup the defaut sort column and order
	protected $datagridValues = array('_sort_order' => 'ASC',
			'_sort_by' => 'domain',);

	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper	->add('domain', null, array('choices' => $this->getSecurityContext()->getToken()->getUser()->getGrantedDomains())) //'choices' => $this->getSecurityContext()->getToken()->getUser()->getGrantedDomains())
					->add('aliasname')
					->add('targets', 'textarea')
					->add('enabled')
					;
	}

	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
            ->add('domain', null, array(), null, array('choices' => $this->getSecurityContext()->getToken()->getUser()->getGrantedDomains()))
			->add('aliasname')
			->add('enabled')
		;
	}

	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
			->add('email')
			->add('enabled')
            ->add('_action', 'actions', array(
                	'actions'	=> array(
                    'edit' 		=> array(),
                    'delete' 	=> array(),
                )
            ))
        ;
	}

	protected function configureShowField(ShowMapper $showMapper)
	{
		$showMapper->add('domain')->add('aliasname')->add('enabled');
	}

	public function validate(ErrorElement $errorElement, $object)
	{
		/*
		 * aliasname	- Needs to fit alias pattern		- Max Length: 255 characters	- Can't be empty
		 * domain 		- Needs to fit domain pattern		- Max Length: 255 characters - Can't be empty
		 * targets		- Each line
		 */

		$maxLengthAliasname = 255;
		$maxLengthEmail = 255;
		$regexAlias = '/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*$/iD';

		$errorElement	->with('aliasname')
							->assertNotBlank()
							->assertMaxLength(array('limit' => $maxLengthAliasname))
							->assertRegex(array('pattern' => $regexAlias))
						->end();

		/* The target's text area need an independendent check since we need to check each line thereof */
		$targetsAlias = $object->getAliasTargets();

		foreach ($targetsAlias as $key => $value)
		{
			$value = trim($value);
			$line = $key + 1;

			if( !$value || !filter_var($value, FILTER_VALIDATE_EMAIL) )
			{
				$errorElement->with('targets')->addViolation("L'adresse email $value (ligne $line) n'est pas valide", array(), "");
			}
			if( mb_strlen($value) > $maxLengthEmail )
			{
				$errorElement->with('targets')->addViolation("L'adresse email $value (ligne $line)  doit avoir au maximum $maxLengthEmail caracteres.", array(), "");
			}
		}
	}

	public function createQuery($context = 'list')
	{
		//Unless the user is s super admin, we want to show only to the super administrator the user that belong to its domain.
		$query = parent::createQuery($context);
		$securityContext = $this->getSecurityContext();
		if( !$securityContext->isGranted('ROLE_SUPER_ADMIN'))
		{
            $user = $securityContext->getToken()->getUser();
            $query->join('o.domain', 'domain')
                  ->join('domain.adminUsers', 'user')
                  ->andWhere('user = :user')
                  ->setParameter('user', $user)
				  ->orderBy('o.domain, o.aliasname');
		} else {
			$query->orderBy('o.domain, o.aliasname');
		}
		return $query;
	}

}

