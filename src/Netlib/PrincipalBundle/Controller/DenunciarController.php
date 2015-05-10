<?php

namespace Netlib\PrincipalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Netlib\PrincipalBundle\Form\DenunciarForm;

class DenunciarController extends Controller {

    /**
     * Controlador de denúncia de usuário
     * @param string $apelido = apelido do usuário que irá ser denunciado
     */
    public function denunciarAction($apelido) {
        $formulario = new DenunciarForm();
        $form = $this->createForm($formulario, $formulario);
        $resultado = $this->receberEmailAction();

        if ($this->get('request')->getMethod() == 'POST') {
            $form->bind($this->get('request'));
            $erros = $this->get('validator')->validate($formulario, array('denunciar'));

            if (count($erros) > 0) {
                return $this->render('NetlibPrincipalBundle:Default:denunciar.html.twig', array('form' => $form->createView(), 'apelido' => $apelido, 'login' => $this->getRequest()->getSession()->get('login'), 'email' => $resultado['email'], 'erro' => true, 'completo' => null));
            } else {
                $this->get('mailer')->send(\Swift_Message::newInstance()
                                ->setSubject($this->get('request')->request->get('denunciar')['seuapelido'] . ' - Denuncia')
                                ->setFrom($resultado['email'])
                                ->setTo('contato@netlib.com.br')
                                ->setBody("Denunciando {$this->get('request')->request->get('denunciar')['apelidodenuncia']} \n\n{$this->get('request')->request->get('denunciar')['motivo']}"));

                return $this->render('NetlibPrincipalBundle:Default:denunciar.html.twig', array('form' => $form->createView(), 'apelido' => $apelido, 'login' => $this->getRequest()->getSession()->get('login'), 'email' => $resultado['email'], 'erro' => null, 'completo' => true));
            }
        }

        return $this->render('NetlibPrincipalBundle:Default:denunciar.html.twig', array('form' => $form->createView(), 'apelido' => $apelido, 'login' => $this->getRequest()->getSession()->get('login'), 'email' => $resultado['email'], 'erro' => null, 'completo' => null));
    }

    /**
     * Função para receber e-mail do usuário logado
     * @return array = array com o e-mail do usuário
     */
    private function receberEmailAction() {
        try {
            return $this->getDoctrine()->getRepository('NetlibPrincipalBundle:Cadastro')->createQueryBuilder('cadastro')
                            ->select('cadastro.email')
                            ->where('cadastro.apelido = :valor')
                            ->setParameter('valor', $this->getRequest()->getSession()->get('login'))
                            ->getQuery()
                            ->getSingleResult();
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao receber email para formulário de denuncia' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao receber email para formulário de denuncia');
        }
    }

}
