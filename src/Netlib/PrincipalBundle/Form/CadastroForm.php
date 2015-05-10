<?php

namespace Netlib\PrincipalBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CadastroForm extends AbstractType {

    public $nome, $apelido, $perfil, $interesses, $sexo, $filtro, $foto1, $foto2, $foto3, $email, $senha, $verificaApelido, $verificaEmail;

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $optionsFiltro = array('choices' => array('Homens', 'Mulheres', 'Ambos'), 'label' => 'Na minha pesquisa quero que apareçam:', 'attr' => array('class' => 'input-area'));
        $optionsSexo = array('choices' => array('Masculino', 'Feminino'), 'attr' => array('class' => 'input-area'));

        $builder->add('nome', 'text', array('required' => true, 'attr' => array('placeholder' => 'Qual é o seu nome?', 'class' => 'input-area')))
                ->add('apelido', 'text', array('required' => true, 'attr' => array('placeholder' => 'Qual será o seu apelido', 'class' => 'input-area')))
                ->add('perfil', 'file', array('required' => true, 'attr' => array('placeholder' => 'Como é o seu rosto?', 'class' => 'input-photo')))
                ->add('interesses', 'textarea', array('required' => true, 'attr' => array('placeholder' => 'O que as pessoas devem saber sobre você? Vale contar desde sua opção sexual, seus interesses até seus valores. Solte a lingua que as pessoas vão te encontrar por este texto. Só não vale ultrapassar 800 caracteres.', 'class' => 'input-text-area')))
                ->add('foto1', 'file', array('required' => false, 'attr' => array('placeholder' => 'Conte mais de você com uma foto ;)(Não obrigatório)', 'class' => 'input-photo')))
                ->add('foto2', 'file', array('required' => false, 'attr' => array('placeholder' => 'Conte mais de você com uma foto ;)(Não obrigatório)', 'class' => 'input-photo')))
                ->add('foto3', 'file', array('required' => false, 'attr' => array('placeholder' => 'Conte mais de você com uma foto ;)(Não obrigatório)', 'class' => 'input-photo')))
                ->add('sexo', 'choice', $optionsSexo)
                ->add('filtro', 'choice', $optionsFiltro)
                ->add('email', 'email', array('required' => true, 'attr' => array('placeholder' => 'Digite seu login/e-mail', 'class' => 'input-area')))
                ->add('senha', 'password', array('required' => true, 'attr' => array('placeholder' => 'Digite sua senha', 'class' => 'input-area')))
                ->add('Enviar', 'submit')
                ->getForm();
    }

    public function getName() {
        return 'cadastro';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Netlib\PrincipalBundle\Form\CadastroForm',
            'validation_groups' => array('cadastro')
        ));
    }

}