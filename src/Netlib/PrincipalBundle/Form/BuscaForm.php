<?php

namespace Netlib\PrincipalBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BuscaForm extends AbstractType {
    
    public $busca;

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('busca', 'text', array('attr' => array('required' => 'required', 'placeholder' => 'O que você procura?', 'class' => 'input-area')))
                ->getForm();
    }

    public function getName() {
        return 'busca';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Netlib\PrincipalBundle\Form\BuscaForm',
            'validation_groups' => array('busca')
        ));
    }

}