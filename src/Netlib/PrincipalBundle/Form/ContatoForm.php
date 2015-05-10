<?php

namespace Netlib\PrincipalBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContatoForm extends AbstractType {
    
    public $nome, $email, $comentario;

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('nome', 'text', array('attr' => array('class' => 'input-area', 'placeholder' => 'Nome', 'required' => 'required')))
                ->add('email', 'text', array('attr' => array('class' => 'input-area', 'placeholder' => 'E-mail', 'required' => 'required')))
                ->add('comentario', 'textarea', array('attr' => array('class' => 'input-text-area-login', 'placeholder' => 'Comentário', 'required' => 'required')))
                ->getForm();
    }

    public function getName() {
        return 'contato';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Netlib\PrincipalBundle\Form\ContatoForm',
            'validation_groups' => array('contato')
        ));
    }

}