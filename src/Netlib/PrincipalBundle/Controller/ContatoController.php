<?php

namespace Netlib\PrincipalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Netlib\PrincipalBundle\Form\ContatoForm;

class ContatoController extends Controller {

    public function contatoAction() {
        $formulario = new ContatoForm();
        $form = $this->createForm($formulario, $formulario);

        if ($this->get('request')->getMethod() == 'POST') {
            $form->bind($this->get('request'));
            $erros = $this->get('validator')->validate($formulario, array('contato'));

            if (count($erros) > 0) {
                return $this->render('NetlibPrincipalBundle:Default:contato.html.twig', array('form' => $form->createView(), 'erro' => true, 'completo' => null));
            } else {
                $this->get('mailer')->send(\Swift_Message::newInstance()
                ->setSubject($this->get('request')->request->get('contato')['nome'] . ' - Contato')
                ->setFrom($this->get('request')->request->get('contato')['email'])
                ->setTo('contato@netlib.com.br')
                ->setBody($this->get('request')->request->get('contato')['comentario']));
                return $this->render('NetlibPrincipalBundle:Default:contato.html.twig', array('form' => $form->createView(), 'erro' => null, 'completo' => true));
            }
        }

        return $this->render('NetlibPrincipalBundle:Default:contato.html.twig', array('form' => $form->createView(), 'erro' => null, 'completo' => null));
    }

}
