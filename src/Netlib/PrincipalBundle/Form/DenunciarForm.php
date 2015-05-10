<?php

namespace Netlib\PrincipalBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DenunciarForm extends AbstractType {
    
    public $seuapelido, $email, $apelidodenuncia, $motivo;

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('seuapelido', 'text', array('attr' => array('class' => 'input-area', 'placeholder' => 'Seu Apelido', 'required' => 'required', 'readonly' => 'readonly')))
                ->add('email', 'text', array('attr' => array('class' => 'input-area', 'placeholder' => 'E-mail', 'required' => 'required', 'readonly' => 'readonly')))
                ->add('apelidodenuncia', 'text', array('attr' => array('class' => 'input-area', 'placeholder' => 'Apelido do Usuário denunciado', 'required' => 'required', 'readonly' => 'readonly')))
                ->add('motivo', 'textarea', array('attr' => array('class' => 'input-text-area-login', 'placeholder' => 'Porque ele está sendo denunciado', 'required' => 'required')))
                ->getForm();
    }

    public function getName() {
        return 'denunciar';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Netlib\PrincipalBundle\Form\DenunciarForm',
            'validation_groups' => array('denunciar')
        ));
    }

}