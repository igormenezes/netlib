<?php

namespace Netlib\PrincipalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AtivacaoController extends Controller {

    private $retorno; //propriedade para controle na verificação do token
    
    /**
     * Controlador para ativação de cadastro
     * @param string $token = valor GET com o número do token de verificação
     */
    public function ativacaoAction($token) {
        $this->verificarTokenAction($token);

        if ($this->retorno) {
            $this->ativarCadastroAction();
            return $this->render('NetlibPrincipalBundle:Default:ativacao.html.twig', array('erro' => false));
        }else{
             return $this->render('NetlibPrincipalBundle:Default:ativacao.html.twig', array('erro' => true));
        }
    }
    
    /**
     * Função para verificar se o código token é o código correto do usuário
     * @param string $token = código para verificação de conta
     * @return array = com apelido do usuário
     */
    private function verificarTokenAction($token) {
        try {
            $this->retorno = $this->getDoctrine()->getRepository('NetlibPrincipalBundle:Cadastro')->createQueryBuilder('cadastro')
                    ->select('cadastro.apelido')
                    ->where('cadastro.token = :valor')
                    ->setParameter('valor', $token)
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
     * Função para ativar cadastro do usuário
     */
    private function ativarCadastroAction() {
        try {
            $cadastro = $this->getDoctrine()->getRepository('NetlibPrincipalBundle:Cadastro')->findOneByApelido($this->retorno['apelido']);
            $cadastro->setAtivo(1);
            $cadastro->setToken(NULL);

            $banco = $this->getDoctrine()->getManager();
            $banco->persist($cadastro);
            $banco->flush();
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao ativar cadastro' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao ativar cadastro');
        }
    }

}
