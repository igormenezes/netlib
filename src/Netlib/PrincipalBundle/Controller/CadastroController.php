<?php

namespace Netlib\PrincipalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Netlib\PrincipalBundle\Form\CadastroForm as CadastroForm;
use Netlib\PrincipalBundle\Manager\ImagemManager as Imagens;
use Netlib\PrincipalBundle\Manager\BuscaManager as Busca;
use Netlib\PrincipalBundle\Manager\TokenManager as Token;
use Netlib\PrincipalBundle\Entity\Cadastro as Cadastro;
use Symfony\Component\HttpFoundation\Response;

class CadastroController extends Controller {

    public function cadastroAction() {
        $formulario = new CadastroForm();
        $form = $this->createForm($formulario, $formulario);

        //condicao para verificacao de apelido
        if ($this->get('request')->request->get('verificaApelido')) {
            return new Response(json_encode($this->verificarApelidoAction($this->get('request')->request->get('verificaApelido'))));
        }
        //condicao para verificacao de email
        elseif ($this->get('request')->request->get('verificaEmail')) {
            return new Response(json_encode($this->verificarEmailAction($this->get('request')->request->get('verificaEmail'))));
        }
        //condicao para validacao do formulario e cadastrar as informacoes no banco
        elseif ($this->get('request')->request->get('cadastro') && !$this->get('request')->request->get('ajaxFoto')) {
            $existeApelido = $this->verificarApelidoAction($this->get('request')->request->get('cadastro')['apelido']);
            $existeEmail = $this->verificarEmailAction($this->get('request')->request->get('cadastro')['email']);

            $form->bind($this->get('request'));

            if (!$form->isValid() || $existeApelido || $existeEmail) {
                $erros = $this->get('validator')->validate($formulario, array('cadastro'));

                if (count($erros) > 0) {
                    foreach ($erros as $erro)
                        $error[$erro->getPropertyPath()] = $erro->getMessage();

                    $json['erros'] = $error;
                }

                $json['mensagem'] = ($existeApelido || $existeEmail) ? true : null;
            } else {
                $token = new Token();
                $codigo = $token->gerarToken($this->get('request')->request->get('cadastro')['apelido']);

                $this->salvarCadastroAction($codigo);

                $this->get('mailer')->send(\Swift_Message::newInstance()
                                ->setSubject('Cadastro Netlib')
                                ->setFrom('contato@netlib.com.br')
                                ->setTo($this->get('request')->request->get('cadastro')['email'])
                                ->setBody("Olá {$this->get('request')->request->get('cadastro')['nome']}, você se cadastrou no Netlib www.netlib.com.br! \n\nPara continuar ative seu cadastro acessando o seguinte link http://netlib.com.br/ativacao/" . $codigo . "\n\nCaso você não se cadastrou no Netlib, nos informe http://netlib.com.br/contato \n\nAtenciosamente, \n\nNetlib."));

                $json = true;
            }
            return new Response(json_encode($json));
        }

        //condicao para realizar upload das fotos por ajax
        elseif ($this->get('request')->request->get('ajaxFoto')) {
            if ($this->get('request')->request->get('foto')) {
                unlink($this->get('request')->request->get('foto'));
                $json = true;
            } else {
                $imagem = new Imagens();
                $json = $imagem->salvarImagensTemporarias($_FILES['cadastro']['tmp_name'][$this->get('request')->request->get('upload')]);
            }
            return new Response(json_encode($json));
        }
        return $this->render('NetlibPrincipalBundle:Default:cadastro.html.twig', array('form' => $form->createView()));
    }

    /**
     * Função para verificar se o apelido está disponivel para cadastro
     * @param string $apelido = apelido para verificar se existe
     * @return boolean = no caso se o apelido nao existir retorna false
     * @return string = retorna o apelido caso existir
     */
    private function verificarApelidoAction($apelido) {
        try {
            return $this->getDoctrine()->getRepository('NetlibPrincipalBundle:Cadastro')->createQueryBuilder('cadastro')
                            ->select('cadastro.apelido')
                            ->where('cadastro.apelido = :valor')
                            ->setParameter('valor', $apelido)
                            ->getQuery()
                            ->getSingleResult();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Função para verificar se o email estÃ¡ disponivel para cadastro
     * @param string $email = email para verificar se existe
     * @return boolean = no caso se o email nao existir retorna false
     * @return string = retorna o email caso existir
     */
    private function verificarEmailAction($email) {
        try {
            return $this->getDoctrine()->getRepository('NetlibPrincipalBundle:Cadastro')->createQueryBuilder('cadastro')
                            ->select('cadastro.email')
                            ->where('cadastro.email = :valor')
                            ->setParameter('valor', $email)
                            ->getQuery()
                            ->getSingleResult();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Função para salvar os dados do formulario no banco de dados
     * @param string $codigo = codigo token para ativação
     */
    private function salvarCadastroAction($codigo) {
        try {
            $cadastro = new Cadastro();
            $imagem = new Imagens();

            $fotos = $imagem->salvarImagens($this->get('request')->request->get('cadastro')['apelido'], 'cadastro');

            $cadastro->setNome($this->get('request')->request->get('cadastro')['nome']);
            $cadastro->setApelido($this->get('request')->request->get('cadastro')['apelido']);
            $cadastro->setEmail($this->get('request')->request->get('cadastro')['email']);
            $cadastro->setSenha(crypt($this->get('request')->request->get('cadastro')['senha']));
            $cadastro->setSexo($this->get('request')->request->get('cadastro')['sexo']);
            $cadastro->setFiltro($this->get('request')->request->get('cadastro')['filtro']);
            $cadastro->setInteresses($this->get('request')->request->get('cadastro')['interesses']);
            $cadastro->setFotos($fotos);
            $cadastro->setStatus(0);
            $cadastro->setAtivo(0);
            $cadastro->setToken($codigo);

            $banco = $this->getDoctrine()->getManager('blogi396_netlib_usuarios');
            $banco->persist($cadastro);
            $banco->flush();
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Ocorreu um erro no cadastro do usuário!' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Ocorreu um erro no cadastro do usuário!');
        }
    }

}
