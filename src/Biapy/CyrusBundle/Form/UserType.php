<?php

namespace Biapy\CyrusBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
//           ->add('username')
            ->add('password')
//            ->add('enabled')
//            ->add('has_mailbox')
//            ->add('is_super_admin')
//            ->add('recovery_token')
//            ->add('recovery_expiry')
//            ->add('domain')
//            ->add('grantedDomains')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Biapy\CyrusBundle\Entity\User'
        ));
    }

    public function getName()
    {
        return 'biapy_cyrusbundle_usertype';
    }
}
