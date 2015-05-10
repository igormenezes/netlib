<?php

namespace Netlib\PrincipalBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EsquecisenhaForm extends AbstractType {
    

    public $login;

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('login', 'text', array('attr' => array('class' => 'input-area', 'placeholder' => 'Seu login', 'required' => 'required')))
                ->getForm();
    }

    public function getName() {
        return 'esquecisenha';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Netlib\PrincipalBundle\Form\EsquecisenhaForm',
            'validation_groups' => array('esquecisenha')
        ));
    }

}