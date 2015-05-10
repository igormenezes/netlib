<?php

namespace Netlib\PrincipalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Netlib\PrincipalBundle\Form\NovasenhaForm;

class NovasenhaController extends Controller {

    private $retorno; //propriedade para controle do codigo do token

    public function novasenhaAction() {
        $formulario = new NovasenhaForm();
        $form = $this->createForm($formulario, $formulario);

        if ($this->get('request')->getMethod() == 'POST') {
            $this->verificarCodigoAction();

            if (!$this->retorno)
                return $this->render('NetlibPrincipalBundle:Default:novasenha.html.twig', array('form' => $form->createView(), 'erro' => 'Código inválido!', 'completo' => null));

            $form->bind($this->get('request'));
            $erros = $this->get('validator')->validate($formulario, array('novasenha'));

            if (count($erros) > 0) {
                return $this->render('NetlibPrincipalBundle:Default:novasenha.html.twig', array('form' => $form->createView(), 'erro' => true, 'completo' => null));
            } else {
                $this->alterarSenhaAction();
                return $this->render('NetlibPrincipalBundle:Default:novasenha.html.twig', array('form' => $form->createView(), 'erro' => null, 'completo' => true));
            }
        }

        return $this->render('NetlibPrincipalBundle:Default:novasenha.html.twig', array('form' => $form->createView(), 'erro' => null, 'completo' => null));
    }

    /**
     * Função para verificar se o código token é válido
     * @return array = retorna o token
     */
    private function verificarCodigoAction() {
        try {
            $this->retorno = $this->getDoctrine()->getRepository('NetlibPrincipalBundle:Cadastro')->createQueryBuilder('cadastro')
                    ->select('cadastro.apelido')
                    ->where('cadastro.token = :valor')
                    ->setParameter('valor', $this->get('request')->request->get('novasenha')['codigo'])
                    ->getQuery()
                    ->getSingleResult();
        } catch (\Exception $e) {
            if (!$this->retorno)
                return;

            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao verificar se o código de token existe' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao verificar se o código de token existe');
        }
    }
    
    /**
     * Função para alterar a senha do usuário
     */
    private function alterarSenhaAction() {
        try {
            $cadastro = $this->getDoctrine()->getRepository('NetlibPrincipalBundle:Cadastro')->findOneByApelido($this->retorno['apelido']);
            $cadastro->setSenha(crypt($this->get('request')->request->get('novasenha')['senha']));
            $cadastro->setToken(NULL);

            $banco = $this->getDoctrine()->getManager();
            $banco->persist($cadastro);
            $banco->flush();
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao alterar a senha' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao alterar a senha');
        }
    }

}
