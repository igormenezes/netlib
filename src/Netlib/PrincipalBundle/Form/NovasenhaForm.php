<?php

namespace Netlib\PrincipalBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class NovasenhaForm extends AbstractType {
    
    public $codigo, $senha;

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('codigo', 'text', array('attr' => array('class' => 'input-area', 'placeholder' => 'Código para ativação', 'required' => 'required')))
                ->add('senha', 'password', array('attr' => array('class' => 'input-area', 'placeholder' => 'Nova senha', 'required' => 'required')))
                ->getForm();
    }

    public function getName() {
        return 'novasenha';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Netlib\PrincipalBundle\Form\NovasenhaForm',
            'validation_groups' => array('novasenha')
        ));
    }

}