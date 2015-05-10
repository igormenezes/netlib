<?php

namespace Netlib\PrincipalBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PerfilForm extends AbstractType {

    public $perfil, $interesses, $filtro, $foto1, $foto2, $foto3, $senha;

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $optionsFiltro = array('choices' => array('Homens', 'Mulheres', 'Ambos'), 'label' => 'Na minha pesquisa quero que apareçam:', 'attr' => array('required' => true, 'class' => 'input-area'));

        $builder->add('perfil', 'file', array('required' => false, 'attr' => array('placeholder' => 'Como é o seu rosto?', 'class' => 'input-photo')))
                ->add('interesses', 'textarea', array('required' => true, 'attr' => array('placeholder' => 'O que as pessoas devem saber sobre você? Vale contar desde sua opção sexual, seus interesses até seus valores. Solte a lingua que as pessoas vão te encontrar por este texto. Só não vale ultrapassar 800 caracteres.', 'class' => 'input-text-area')))
                ->add('foto1', 'file', array('required' => false, 'attr' => array('placeholder' => 'Conte mais de você com uma foto ;)(Não obrigatório)', 'class' => 'input-photo')))
                ->add('foto2', 'file', array('required' => false, 'attr' => array('placeholder' => 'Conte mais de você com uma foto ;)(Não obrigatório)', 'class' => 'input-photo')))
                ->add('foto3', 'file', array('required' => false, 'attr' => array('placeholder' => 'Conte mais de você com uma foto ;)(Não obrigatório)', 'class' => 'input-photo')))
                ->add('filtro', 'choice', $optionsFiltro)
                ->add('senha', 'password', array('required' => false, 'attr' => array('placeholder' => 'Nova senha (Não obrigatório)', 'class' => 'input-area')))
                ->getForm();
    }

    public function getName() {
        return 'perfil';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Netlib\PrincipalBundle\Form\PerfilForm',
            'validation_groups' => array('perfil')
        ));
    }

}
