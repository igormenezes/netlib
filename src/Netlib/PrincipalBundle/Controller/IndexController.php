<?php

namespace Netlib\PrincipalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Netlib\PrincipalBundle\Form\IndexForm as IndexForm;

class IndexController extends Controller {

    private $retornoLogin; //propriedade que realiza o login
    private $retornoCadastro; //propriedade que verifica cadastro e se está ativo
    private $logado; //propriedade para controle, true  = login ok, false = login false

    public function indexAction() {
        if ($this->getRequest()->getSession()->has('login'))
            return $this->redirect($this->generateUrl('netlib_home'));

        $formulario = new IndexForm();
        $form = $this->createForm($formulario, $formulario);

        if ($this->get('request')->getMethod() == 'POST') {
            $form->bind($this->get('request'));
            $erros = $this->get('validator')->validate($formulario, array('padrao'));

            if (count($erros) > 0) {
                return $this->render('NetlibPrincipalBundle:Default:index.html.twig', array('form' => $form->createView(), 'erro' => 'Login ou senha inválidos'));
            } else {
                $this->logarAction();

                if (!$this->retornoLogin || !$this->logado)
                    return $this->render('NetlibPrincipalBundle:Default:index.html.twig', array('form' => $form->createView(), 'erro' => 'Login ou senha inválidos'));

                $ativo = $this->verificarCadastroAtivo();

                if (!$ativo['ativo'])
                    return $this->render('NetlibPrincipalBundle:Default:index.html.twig', array('form' => $form->createView(), 'erro' => 'Seu cadastro ainda não foi ativado. Utilize o código que recebeu no e-mail para ativar!'));

                $this->online($this->retornoLogin['apelido']);
                $this->getRequest()->getSession()->set('login', $this->retornoLogin['apelido']);
                return $this->redirect($this->generateUrl('netlib_home'));
            }
        }

        return $this->render('NetlibPrincipalBundle:Default:index.html.twig', array('form' => $form->createView(), 'erro' => null));
    }

    /**
     * Função para realizar login no site
     */
    private function logarAction() {
        try {
            $this->retornoLogin = $this->getDoctrine()->getRepository('NetlibPrincipalBundle:Cadastro')->createQueryBuilder('cadastro')
                    ->select('cadastro.senha', 'cadastro.apelido')
                    ->where('cadastro.email = :email')
                    ->setParameter('email', $this->get('request')->request->get('padrao')['login'])
                    ->getQuery()
                    ->getSingleResult();

            if (crypt($this->get('request')->request->get('padrao')['senha'], $this->retornoLogin['senha']) === $this->retornoLogin['senha'])
                $this->logado = true;
        } catch (\Exception $e) {
            if (!$this->retornoLogin)
                return;
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao logar' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao logar');
        }
    }

    /**
     * Função para verificar se o cadastro está ativo
     * @return array = retorna se o cadastro está ativo ou não
     */
    private function verificarCadastroAtivo() {
        try {
            return $this->getDoctrine()->getRepository('NetlibPrincipalBundle:Cadastro')->createQueryBuilder('cadastro')
                            ->select('cadastro.ativo')
                            ->where('cadastro.email = :email')
                            ->setParameter('email', $this->get('request')->request->get('padrao')['login'])
                            ->getQuery()
                            ->getSingleResult();
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao verificar se o cadastro está ativo' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao verificar se o cadastro está ativo');
        }
    }

    /**
     * Função para atualizar o status do usuario para online
     * @param $apelido = apelido do usuario que está logando no sistema
     */
    private function online($apelido) {
        try {
            $cadastro = $this->getDoctrine()->getRepository('NetlibPrincipalBundle:Cadastro')->findOneByApelido($apelido);
            $cadastro->setStatus(1);
            $banco = $this->getDoctrine()->getManager();
            $banco->persist($cadastro);
            $banco->flush();
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao atualizar status do usuario para online' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao atualizar status do usuario para online');
        }
    }

}
