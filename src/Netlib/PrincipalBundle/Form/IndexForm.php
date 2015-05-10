<?php

namespace Netlib\PrincipalBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class IndexForm extends AbstractType {
    
    public $login, $senha;

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('login', 'text', array('attr' => array('class'=> 'input-area', 'placeholder' => 'Login')))
                ->add('senha', 'password', array('attr' => array('class'=> 'input-area', 'placeholder' => 'Senha')))
                ->add('Entrar', 'submit')
                ->getForm();
    }

    public function getName() {
        return 'padrao';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Netlib\PrincipalBundle\Form\IndexForm',
            'validation_groups' => array('padrao')
        ));
    }

}