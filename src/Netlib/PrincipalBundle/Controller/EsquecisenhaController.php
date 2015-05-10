<?php

namespace Netlib\PrincipalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Netlib\PrincipalBundle\Form\EsquecisenhaForm;
use Netlib\PrincipalBundle\Manager\TokenManager as Token;

class EsquecisenhaController extends Controller {

    private $retornoApelido; //propriedade que recebe o apelido, para ter controle de retorno

    public function esquecisenhaAction() {
        $formulario = new EsquecisenhaForm();
        $form = $this->createForm($formulario, $formulario);

        if ($this->get('request')->getMethod() == 'POST') {
            $this->receberApelidoAction();

            if (!$this->retornoApelido)
                return $this->render('NetlibPrincipalBundle:Default:esquecisenha.html.twig', array('form' => $form->createView(), 'erro' => 'login/e-mail inválido', 'completo' => null));

            $form->bind($this->get('request'));
            $erros = $this->get('validator')->validate($formulario, array('esquecisenha'));

            if (count($erros) > 0) {
                return $this->render('NetlibPrincipalBundle:Default:esquecisenha.html.twig', array('form' => $form->createView(), 'erro' => true, 'completo' => null));
            } else {
                $token = new Token();
                $codigo = $token->gerarToken($this->retornoApelido['apelido']);
                $this->salvarTokenAction($codigo);
                $this->get('mailer')->send(\Swift_Message::newInstance()
                                ->setSubject($this->retornoApelido['apelido'] . ' - Esqueci Senha')
                                ->setFrom('contato@netlib.com.br')
                                ->setTo($this->get('request')->request->get('esquecisenha')['login'])
                                ->setBody('Seu código para recuperar a senha é ' . $codigo));

                return $this->render('NetlibPrincipalBundle:Default:esquecisenha.html.twig', array('form' => $form->createView(), 'erro' => null, 'completo' => true));
            }
        }

        return $this->render('NetlibPrincipalBundle:Default:esquecisenha.html.twig', array('form' => $form->createView(), 'erro' => null, 'completo' => null));
    }

    /**
     * Retorna o apelido do usuário
     * @return array = apelido do usuário
     */
    private function receberApelidoAction() {
        try {
            $this->retornoApelido = $this->getDoctrine()->getRepository('NetlibPrincipalBundle:Cadastro')->createQueryBuilder('cadastro')
                            ->select('cadastro.apelido')
                            ->where('cadastro.email = :valor')
                            ->setParameter('valor', $this->get('request')->request->get('esquecisenha')['login'])
                            ->getQuery()
                            ->getSingleResult();
        } catch (\Exception $e) {
            if (!$this->retornoApelido)
                return;

            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao receber apelido do formul[ario esqueci minha senha' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao receber apelido do formul[ario esqueci minha senha');
        }
    }

    /**
     * Função para salvar token do usuário no banco de dados
     * @param string $codigo = codigo de verificação para alterar nova senha
     */
    private function salvarTokenAction($codigo) {
        try {
            $cadastro = $this->getDoctrine()->getRepository('NetlibPrincipalBundle:Cadastro')->findOneByApelido($this->retornoApelido['apelido']);
            $cadastro->setToken($codigo);

            $banco = $this->getDoctrine()->getManager();
            $banco->persist($cadastro);
            $banco->flush();
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao salvar token' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao salvar token');
        }
    }

}
