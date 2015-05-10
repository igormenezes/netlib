<?php

namespace Netlib\PrincipalBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MensagemForm extends AbstractType {
    
    public $mensagem;

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('mensagem', 'text', array('attr' => array('required' => 'required', 'class' => 'input-area')))
                ->getForm();
    }

    public function getName() {
        return 'mensagem';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Netlib\PrincipalBundle\Form\MensagemForm',
            'validation_groups' => array('mensagem')
        ));
    }

}