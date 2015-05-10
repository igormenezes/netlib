<?php

namespace Netlib\PrincipalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Netlib\PrincipalBundle\Form\PerfilForm as PerfilForm;
use Netlib\PrincipalBundle\Form\BuscaForm as BuscaForm;
use Netlib\PrincipalBundle\Form\MensagemForm as MensagemForm;
use Netlib\PrincipalBundle\Manager\ImagemManager as Imagens;
use Netlib\PrincipalBundle\Manager\BuscaManager as Busca;
use Netlib\PrincipalBundle\Manager\MensagemManager as Mensagem;
use Netlib\PrincipalBundle\Entity\CacheBusca as CacheBusca;
use Netlib\PrincipalBundle\Entity\BuscaRealizada as BuscaRealizada;
use Netlib\PrincipalBundle\Entity\BuscaResultado as BuscaResultado;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller {

    private $limite = 30; //propriedade para limite de resultados na busca
    private $usuariosHome = 20; //propriedade para limite para quantidade de usuários a serem exibidos na home

    public function homeAction() {
        $formularioPerfil = new PerfilForm();
        $formularioBusca = new BuscaForm();
        $formularioMensagem = new MensagemForm();

        $formPerfil = $this->createForm($formularioPerfil, $formularioPerfil);
        $formBusca = $this->createForm($formularioBusca, $formularioBusca);
        $formMensagem = $this->createForm($formularioMensagem, $formularioMensagem);

        //condicao verifica a sessao de login
        if (!$this->getRequest()->getSession()->has('login'))
            return $this->redirect($this->generateUrl('netlib_index'));

        //-------HOME RECEBER ALGUNS USUÀRIOS DO SITE--------//
        //condicao para receber usuários aleatorios para apresentar na home para o usuário logado
        if ($this->get('request')->request->get('buscarUsuariosHome')) {
            $json['dados'] = $this->receberUsuariosHomeAction();
            if ($json['dados'])
                return new Response(json_encode($json));

            return new Response(json_encode(false));
        }

        //-------HOME ALTERAR/VERIFICAR STATUS PARA ONLINE--------//
        //condicao para verificar status do usuario, forçar que o usuario está online, caso nao esteja setado no banco
        else if ($this->get('request')->request->get('status')) {
            $this->alterarStatusOnlineAction();
            return new Response(json_encode(true));
        }

        //-------PERFIL FORMULARIO--------//
        //condicao para validacao do formulario de perfil e envio de dados para o banco de dados
        else if ($this->get('request')->request->get('perfil') && !$this->get('request')->request->get('ajaxFoto')) {
            $formPerfil->bind($this->get('request'));
            $erros = $this->get('validator')->validate($formularioPerfil, array('perfil'));

            if (count($erros) > 0) {
                foreach ($erros as $erro)
                    $error[$erro->getPropertyPath()] = $erro->getMessage();

                $json['erros'] = $error;
            } else {
                $this->alterarPerfilAction();
                $json = true;
            }
            return new Response(json_encode($json));
        }

        //-------PERFIL DO USU�?RIO--------//
        //condicao clicar no botao perfil do menu, trazer os dados do perfil usuário para edição
        else if ($this->get('request')->request->get('botaoPerfil')) {
            $retorno = $this->receberPerfilUsuarioAction();
            return new Response(json_encode($retorno));
        }

        //-------PERFIL DA BUSCA--------//
        //condicao clicar nos perfis do resultado da busca
        else if ($this->get('request')->request->get('perfilBusca')) {
            $retorno = $this->receberPerfilBuscaAction($this->get('request')->request->get('perfilBusca'));
            return new Response(json_encode($retorno));
        }

        //-------PERFIL ENVIAR FOTO AJAX--------//
        //condicao upload de foto por ajax
        else if ($this->get('request')->request->get('ajaxFoto')) {
            if ($this->get('request')->request->get('foto')) {
                unlink($this->get('request')->request->get('foto'));
                $json = true;
            } else {
                $imagem = new Imagens();
                $json = $imagem->salvarImagensTemporarias($_FILES['perfil']['tmp_name'][$this->get('request')->request->get('upload')]);
            }
            return new Response(json_encode($json));
        }

        //-------PERFIL REMOVER FOTO AJAX--------//
        //condicao para remover foto por ajax
        else if ($this->get('request')->request->get('pastaFoto') && $this->get('request')->request->get('nomeFoto')) {
            if (file_exists($this->get('request')->request->get('pastaFoto')))
                $this->removerFotoAction($this->get('request')->request->get('nomeFoto'));
            return new Response(json_encode(true));
        }

        //-------BUSCA--------//
        //condicao para validacao do formulario de busca e realizar a busca no banco de dados
        else if ($this->get('request')->request->get('busca')) {
            $formBusca->bind($this->get('request'));
            $erros = $this->get('validator')->validate($formularioBusca, array('busca'));
            if (count($erros) > 0) {
                foreach ($erros as $erro)
                    $error[$erro->getPropertyPath()] = $erro->getMessage();
                $json['erros'] = $error;
                return new Response(json_encode($json));
            } else {
                $json['dados'] = $this->buscarAction();

                if ($json['dados']) {
                    $json['carregar'] = $this->limite;
                    return new Response(json_encode($json));
                }
            }
            return new Response(json_encode(false));
        }

        //-------BUSCA SCROLL--------//
        //condicao para carregar o restante da busca por ajax
        else if ($this->get('request')->request->get('scroll') && !$this->get('request')->request->get('random')) {
            $json['dados'] = $this->buscarAction();
            if ($json['dados']) {
                $json['carregar'] = $this->get('request')->request->get('carregar') + $this->limite;
                return new Response(json_encode($json));
            }

            return new Response(json_encode(false));
        }

        //-------BUSCA RANDOM--------//
        //condicao para buscar usuarios existentes
        else if ($this->get('request')->request->get('buscaRandom')) {
            $array = $this->receberFiltroAction();
            $dados = $this->pesquisarUsuariosAction($array['filtro']);

            if ($dados) {
                $busca = new Busca();
                $json['dados'] = $busca->filtrarFotosBusca($dados);
                $json['carregar'] = $this->limite;
                return new Response(json_encode($json));
            }

            return new Response(json_encode(false));
        }

        //-------BUSCA SCROLL RANDOM--------//
        //condicao para carregar o restante dos usuarios existentes
        else if ($this->get('request')->request->get('random')) {
            $array = $this->receberFiltroAction();
            $dados = $this->pesquisarUsuariosAction($array['filtro']);

            if ($dados) {
                $busca = new Busca();
                $json['dados'] = $busca->filtrarFotosBusca($dados);
                $json['carregar'] = $this->get('request')->request->get('carregar') + $this->limite;
                return new Response(json_encode($json));
            }

            return new Response(json_encode(false));
        }

        //-------FAVORITOS ADICIONAR USUARIO--------//
        //condicao para adicionar usuário aos favoritos
        else if ($this->get('request')->request->get('adicionarFavoritos')) {
            $this->adicionarFavoritosAction();
            return new Response(json_encode(true));
        }

        //-------FAVORITOS VERIFICAR USUARIO--------//
        //condicao para verificar se o usuário já foi adicionado nos favoritos
        else if ($this->get('request')->request->get('verificarFavoritos')) {
            $json = $this->verificarFavoritosAction();
            return new Response(json_encode($json));
        }

        //-------FAVORITOS RECEBER USUARIOS--------//
        //condicao para receber todos os usuários dos favoritos do usuário que está logado
        else if ($this->get('request')->request->get('botaoFavoritos')) {
            $retorno = $this->receberFavoritosAction();

            if ($retorno['favoritos']) {
                $json = $this->receberPerfilFavoritosAction($retorno['favoritos']);
            } else {
                $json = false;
            }
            return new Response(json_encode($json));
        }

        //-------FAVORITOS EXCLUIR USUARIOS--------//
        //condicao para excluir usuário dos favoritos
        else if ($this->get('request')->request->get('excluirFavoritos')) {
            $this->excluirFavoritosAction();
            return new Response(json_encode(true));
        }

        //-------SAIR--------//
        //condicao clicar no botao sair do menu
        else if ($this->get('request')->request->get('botaoSair')) {
            $this->offlineAction();
            $this->getRequest()->getSession()->clear();
            return new Response(json_encode(true));
        }

        //-------MENSAGEM RECEBER USUARIOS--------//
        //condicao para mostrar os usuarios no qual eu mantive alguma conversa
        else if ($this->get('request')->request->get('botaoMensagem')) {
            $mensagem = new Mensagem($this->getRequest()->getSession()->get('login'));
            $dados = $mensagem->receberMensagens();

            if ($dados) {
                $json['contatos'] = $this->receberDadosContatosChatAction($dados['contatos']);
                $json['notificacao'] = $mensagem->receberNotificacoes();
                return new Response(json_encode($json));
            }

            return new Response(json_encode(false));
        }

        //-------MENSAGEM ABRIR CHAT--------//
        //condicao para abrir chat com o usuario selecionado e receber os outros usuarios que eu mantive conversa
        else if ($this->get('request')->request->get('chat') && !$this->get('request')->request->get('notificacao')) {
            $json['perfil'] = $this->receberPerfilSelecionadoMensagemAction();
            $mensagem = new Mensagem($this->getRequest()->getSession()->get('login'));
            $dados = $mensagem->receberMensagens($this->get('request')->request->get('chat'));

            if (isset($dados['contatos'])) {
                $json['contatos'] = $this->receberDadosContatosChatAction($dados['contatos']);
                $json['notificacao'] = $mensagem->receberNotificacoes();
            }

            if (isset($dados['conversa'])) {
                $json['quantidadeMensagensChat'] = $dados['quantidadeMensagensChat'];
                $json['conversa'] = $dados['conversa'];
                $json['limite'] = (isset($dados['limite'])) ? $dados['limite'] : false;
                $json['termino'] = (isset($dados['termino'])) ? $dados['termino'] : true;
            }

            $json['login'] = $this->getRequest()->getSession()->get('login');
            return new Response(json_encode($json));
        }

        //-------MENSAGEM CARREGAR HISTORICO--------//
        //condicao para carregar historico de mensagens do usuario selecionado
        else if ($this->get('request')->request->get('carregarHistorico')) {
            $mensagem = new Mensagem($this->getRequest()->getSession()->get('login'));
            $dados = $mensagem->carregarHistorico($this->get('request')->request->get('carregarHistorico'), $this->get('request')->request->get('posicao'));

            if ($dados) {
                $json['conversa'] = $dados['conversa'];
                $json['limite'] = $dados['limite'];
                $json['termino'] = $dados['termino'];
                $json['login'] = $this->getRequest()->getSession()->get('login');
                return new Response(json_encode($json));
            } else {
                return new Response(json_encode(false));
            }
        }

        //-------MENSAGEM ENVIAR SUBMIT--------//
        //condicao para validacao da mensagem para envio
        else if ($this->get('request')->request->get('mensagemDestinatario')) {
            $formMensagem->bind($this->get('request'));
            $erros = $this->get('validator')->validate($formularioMensagem, array('mensagem'));

            if (count($erros) > 0) {
                foreach ($erros as $erro)
                    $error[$erro->getPropertyPath()] = $erro->getMessage();

                $json['erros'] = $error;
            } else {
                $mensagem = new Mensagem($this->getRequest()->getSession()->get('login'));
                /* Salvar mensagem no arquivo do remetente */ $json = $mensagem->salvarMensagem($this->get('request')->request->get('mensagem')['mensagem'], $this->get('request')->request->get('mensagemDestinatario'));

                if (!$json) {
                    $json['capacidadeEsgotada'] = true;
                    return new Response(json_encode($json));
                }

                /* Salvar mensagem no arquivo do destinatario */ $mensagem->salvarMensagem($this->get('request')->request->get('mensagem')['mensagem'], $this->get('request')->request->get('mensagemDestinatario'), false, true);
                /* Salvar backup no arquivo do remetente */ $mensagem->salvarMensagem($this->get('request')->request->get('mensagem')['mensagem'], $this->get('request')->request->get('mensagemDestinatario'), true);
                /* Salvar backup no arquivo do destinatario */ $mensagem->salvarMensagem($this->get('request')->request->get('mensagem')['mensagem'], $this->get('request')->request->get('mensagemDestinatario'), true, true);
            }
            return new Response(json_encode($json));
        }

        //-------MENSAGEM LIMPAR HISTORICO--------//
        //condicao limpar historico do usuario selecionado
        else if ($this->get('request')->request->get('limparHistorico')) {
            $mensagem = new Mensagem($this->getRequest()->getSession()->get('login'));
            $mensagem->limparHistorico($this->get('request')->request->get('limparHistorico'));
            return new Response(json_encode(true));
        }

        //-------MENSAGEM RECEBER NOTIFICACAO--------//
        //condicao para receber notificacao de novas mensagens e de quem enviou a mensangem
        else if ($this->get('request')->request->get('notificacao') && !$this->get('request')->request->get('mensagensChat')) {
            $mensagem = new Mensagem($this->getRequest()->getSession()->get('login'));
            $json = $mensagem->receberNotificacoes();

            if ($json)
                return new Response(json_encode($json));
            else
                return new Response(json_encode(false));
        }

        //-------MENSAGEM RECEBER NOTIFICACAO DAS ÙLTIMAS MENSAGENS DO CHAT--------//
        //condicao para receber ultimas mensagens da conversa com a atual pessoa do chat
        else if ($this->get('request')->request->get('notificacao') && $this->get('request')->request->get('mensagensChat')) {
            $mensagem = new Mensagem($this->getRequest()->getSession()->get('login'));
            $json = $mensagem->receberMensagensChat($this->get('request')->request->get('chat'), $this->get('request')->request->get('mensagensChat'));

            if ($json)
                return new Response(json_encode($json));

            return new Response(json_encode(false));
        }

        //-------MENSAGEM LIMPAR NOTIFICACAO--------//
        //condicao para limpar notificacao de mensagem recebida do usuario clicado, pois já foi visualizada
        else if ($this->get('request')->request->get('limparNotificacao')) {
            $mensagem = new Mensagem($this->getRequest()->getSession()->get('login'));
            $mensagem->apagarNotificacao($this->get('request')->request->get('limparNotificacao'));
            return new Response(json_encode(true));
        }

        //-------MENSAGEM RECEBER STATUS USUARIOS--------//
        //condicao para receber status dos usuarios de contato do chat
        else if ($this->get('request')->request->get('receberStatus')) {
            $json['usuarioStatus'] = $this->receberStatusUsuariosAction($this->get('request')->request->get('receberStatus'));
            return new Response(json_encode($json));
        }

        //-------MENSAGEM RECEBER CONTATOS CHAT--------//
        //condicao para receber lista de contatos do chat atualizada
        else if ($this->get('request')->request->get('receberContatos')) {
            $mensagem = new Mensagem($this->getRequest()->getSession()->get('login'));
            $dados = $mensagem->receberContatos();

            if (isset($dados['contatos'])) {
                $json['contatos'] = $this->receberFotosContatosAction($dados['contatos']);
                return new Response(json_encode($json));
            }

            return new Response(json_encode(false));
        }

        return $this->render('NetlibPrincipalBundle:Default:home.html.twig', array('formPerfil' => $formPerfil->createView(), 'formBusca' => $formBusca->createView(), 'formMensagem' => $formMensagem->createView()));
    }

    //---------------------------------------------MÉTODOS HOME-------------------------------------------------//

    /**
     * Função para receber usuários para exibir na home
     * @return array = usuarios
     */
    private function receberUsuariosHomeAction() {
        try {
            $id = $this->getRequest()->getSession()->get('login');
            
            $query = "SELECT `apelido`, `fotos` FROM `cadastro` WHERE `apelido` <> ? AND `ativo` = 1 ORDER BY RAND() LIMIT $this->usuariosHome";
            $pdo = $this->container->get('blogi396_netlib_usuarios');
            $statement = $pdo->prepare($query);
            $statement->bindParam(1, $id, \PDO::PARAM_INT);
            $statement->execute();
            $resultado = $statement->fetchAll();

            if ($resultado) {
                $busca = new Busca();
                return $busca->filtrarFotosBusca($resultado);
            }

            return false;
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao receber usuários para exibir na home' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao receber usuários para exibir na home');
        }
    }

    /**
     * Função para alterar status do usuário para online
     */
    private function alterarStatusOnlineAction() {
        try {
            $cadastro = $this->getDoctrine()->getRepository('NetlibPrincipalBundle:Cadastro')->findOneByApelido($this->getRequest()->getSession()->get('login'));
            $cadastro->setStatus(1);

            $banco = $this->getDoctrine()->getManager();
            $banco->persist($cadastro);
            $banco->flush();
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao alterar/verificar status para online' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao alterar/verificar status para online');
        }
    }

    //-----------------------------------MÉTODOS PERFIL-------------------------------------------//

    /**
     * Função para receber os dados do usuario logado, para realizar alteração do perfil
     * @return array = retorna um array com os dados do perfil do usuario
     */
    private function receberPerfilUsuarioAction() {
        try {
            return $this->getDoctrine()->getRepository('NetlibPrincipalBundle:Cadastro')->createQueryBuilder('cadastro')
                            ->select('cadastro.apelido', 'cadastro.filtro', 'cadastro.interesses', 'cadastro.fotos')
                            ->where('cadastro.apelido = :valor')
                            ->setParameter('valor', $this->getRequest()->getSession()->get('login'))
                            ->getQuery()
                            ->getSingleResult();
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao receber dados do perfil do usuário' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao receber dados do perfil do usuário');
        }
    }

    /**
     * Função para receber os dados dos usuarios da busca
     * @param string $apelido = apelido do usuário selecionado
     * @return array = retorna um array com os dados do perfil do usuario da busca
     */
    private function receberPerfilBuscaAction($apelido) {
        try {
            return $this->getDoctrine()->getRepository('NetlibPrincipalBundle:Cadastro')->createQueryBuilder('cadastro')
                            ->select('cadastro.apelido', 'cadastro.interesses', 'cadastro.fotos')
                            ->where('cadastro.apelido = :valor')
                            ->setParameter('valor', $apelido)
                            ->getQuery()
                            ->getSingleResult();
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao receber dados do perfil da busca' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao receber dados do perfil da busca');
        }
    }

    /**
     * Função para receber perfis para a sessão de favoritos
     * @param array $favoritos = array com os usuarios que estão no favoritos
     * @return array = array contendo o apelido e fotos de cada usuário do favoritos
     */
    private function receberPerfilFavoritosAction($favoritos) {
        try {
            $apelidos = explode('|', $favoritos);
            $i = 0;
            $query = $this->getDoctrine()->getRepository('NetlibPrincipalBundle:Cadastro')->createQueryBuilder('cadastro');
            $query->select('cadastro.apelido, cadastro.fotos');
            foreach ($apelidos as $val) {
                if ($i == 0)
                    $query->where("cadastro.apelido = :valor{$i}");
                else
                    $query->orWhere("cadastro.apelido = :valor{$i}");

                $query->setParameter("valor{$i}", $val);
                $i++;
            }

            return $query->getQuery()->getResult();
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao receber dados do usuário que está nos favoritos' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao receber dados do usuário que está nos favoritos');
        }
    }

    /**
     * Função para receber perfil do usuario para conversa no chat
     * @return array = array contendo foto e apelido do usuario selecionado para conversa no chat
     */
    private function receberPerfilSelecionadoMensagemAction() {
        try {
            return $this->getDoctrine()->getRepository('NetlibPrincipalBundle:Cadastro')->createQueryBuilder('cadastro')
                            ->select('cadastro.fotos', 'cadastro.apelido')
                            ->where('cadastro.apelido = :valor')
                            ->setParameter('valor', $this->get('request')->request->get('chat'))
                            ->getQuery()
                            ->getSingleResult();
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao receber dados do perfil selecionado para envio de mensagem' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao receber dados do perfil selecionado para envio de mensagem');
        }
    }

    /**
     * Função para alterar dados do perfil, de acordo com os dados do formulario de edição de perfil
     */
    private function alterarPerfilAction() {
        try {
            $cadastro = $this->getDoctrine()->getRepository('NetlibPrincipalBundle:Cadastro')->findOneByApelido($this->getRequest()->getSession()->get('login'));

            foreach ($cadastro as $key => $val)
                $perfil[$key] = $val;

            if ($_FILES) {
                $imagem = new Imagens();
                $fotos = $imagem->salvarImagens($this->getRequest()->getSession()->get('login'), 'perfil');
                $cadastro->setFotos($fotos);
            }

            if ($this->get('request')->request->get('perfil')['interesses'] != $perfil['interesses']) {
                $cadastro->setInteresses($this->get('request')->request->get('perfil')['interesses']);
            }

            $cadastro->setFiltro($this->get('request')->request->get('perfil')['filtro']);

            if ($this->get('request')->request->get('perfil')['senha'])
                $cadastro->setSenha(crypt($this->get('request')->request->get('perfil')['senha']));

            $banco = $this->getDoctrine()->getManager();
            $banco->persist($cadastro);
            $banco->flush();
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao atualizar informações do perfil' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao atualizar informações do perfil');
        }
    }

    /**
     * Função para remover a foto desejeda do banco de dados e dos arquivos do sistema
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function removerFotoAction() {
        try {
            $string = $this->getDoctrine()->getRepository('NetlibPrincipalBundle:Cadastro')->createQueryBuilder('cadastro')
                    ->select('cadastro.fotos')
                    ->where('cadastro.apelido = :valor')
                    ->setParameter('valor', $this->getRequest()->getSession()->get('login'))
                    ->getQuery()
                    ->getSingleResult();

            unlink($this->get('request')->request->get('pastaFoto'));
            $arrayFotos = explode('|', $string['fotos']);

            foreach ($arrayFotos as $chave => $val) {
                if ($val == $this->get('request')->request->get('nomeFoto'))
                    unset($arrayFotos[$chave]);
            }

            $fotos = implode('|', $arrayFotos);

            $cadastro = $this->getDoctrine()->getRepository('NetlibPrincipalBundle:Cadastro')->findOneByApelido($this->getRequest()->getSession()->get('login'));
            $cadastro->setFotos($fotos);
            $banco = $this->getDoctrine()->getManager();
            $banco->persist($cadastro);
            $banco->flush();
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao remover fotos do banco de dados' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao remover fotos do banco de dados');
        }
    }

    //---------------------------------------------MÉTODOS BUSCA-------------------------------------------------//

    /**
     * Função principal para realizar as chamadas de busca, tatno novas busca, como continuar busca atual de acordo com scroll
     * @return array = retorna um array com os usuarios que possuem os interesses buscados
     */
    private function buscarAction() {
        $busca = new Busca();
        $resultado = $this->receberIdAction();

        if ($this->get('request')->request->get('scroll')) {
            $interessesFiltrados = $busca->filtrarInteresses($_COOKIE['interesse']);
        } else {
            setcookie("interesse", $this->get('request')->request->get('busca')['busca'], time() + 3600);
            $idBusca = $this->salvarCacheBuscaAction($resultado['id']);
            setcookie("idBusca", $idBusca, time() + 3600);
            $interessesFiltrados = $busca->filtrarInteresses($this->get('request')->request->get('busca')['busca']);
            $this->salvarBuscaRealizadaAction($interessesFiltrados, $idBusca);
        }

        $array = $this->receberFiltroAction();
        $retorno = $this->pesquisarInteressesAction($interessesFiltrados, $array['filtro']);

        if (!$retorno)
            return false;

        if ($retorno) {
            $id = (isset($idBusca)) ? $idBusca : $_COOKIE['idBusca'];
            $interesse = ($this->get('request')->request->get('busca')['busca']) ? $this->get('request')->request->get('busca')['busca'] : $_COOKIE['interesse'];
            $this->salvarBuscaResultadoAction($retorno, $id, $interesse);

            return $busca->filtrarFotosBusca($retorno);
        }

        return false;
    }

    /**
     * Função para salvar o que o usuario buscou, para ter salvo em tabela de cache para futuras analises do site
     * @param $id = id do usuario
     * @return integer = retorna o id da busca
     */
    private function salvarCacheBuscaAction($id) {
        try {
            $cache = new CacheBusca();
            $cache->setIdUsuarioPesquisa($id);
            $cache->setInteresseBusca($this->get('request')->request->get('busca')['busca']);

            $banco = $this->getDoctrine()->getManager('blogi396_netlib_busca');
            $banco->persist($cache);
            $banco->flush();

            return $cache->getId();
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao salvar cache da busca' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao salvar cache da busca');
        }
    }

    /**
     * Função para salvar as tags da busca em uma tabela para futuras analises do site
     * @param array $interessesFiltrados = array contendo as tags pesquisadas
     * @param integer $idBusca = id do cahce de busca, que represeta a atual busca
     */
    private function salvarBuscaRealizadaAction($interessesFiltrados, $idBusca) {
        try {
            $banco = $this->getDoctrine()->getManager('blogi396_netlib_busca');

            foreach ($interessesFiltrados as $val) {
                $buscaRealizada = new BuscaRealizada();
                $buscaRealizada->setCacheBuscaId($idBusca);
                $buscaRealizada->setTagPesquisa($val);
                $banco->persist($buscaRealizada);
            }

            $banco->flush();
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao salvar dados da busca realizada' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao salvar dados da busca realizada');
        }
    }

    /**
     * Função para salvar os dados da busca que teve algum resultado
     * @param array $array = array multidimensional contendo os apelidos e fotos de usuarios
     * @param integer $idBusca = id do cahce de busca, que represeta a atual busca
     * @param string $interesse = string com os interesses inseridos na busca
     */
    private function salvarBuscaResultadoAction($array, $idBusca, $interesse) {
        try {
            $banco = $this->getDoctrine()->getManager('blogi396_netlib_busca');

            foreach ($array as $valores) {
                $buscaResultado = new BuscaResultado();
                $buscaResultado->setCacheBuscaId($idBusca);
                $buscaResultado->setIdUsuarioResultado($valores['apelido']);
                $buscaResultado->setInteresseResultado($interesse);
                $banco->persist($buscaResultado);
            }

            $banco->flush();
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao salvar dados de resultado da busca' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao salvar dados de resultado da busca');
        }
    }

    /**
     * Função para retornar id do usuario, que existe aquela tag em seus interesses. Assim retorna resultado da busca realizada
     * @param array $interessesFiltrados = contendo as tagas pesquisadas
     * @param integer $filtro = contendo o filtro do usuário logado
     * $return array = retorna um array multidimensional contendo os ids de usuarios
     */
    private function pesquisarInteressesAction($interessesFiltrados, $filtro) {
        try {
            $i = 1;

            if ($filtro != 2)
                $sql = "SELECT cadastro.apelido, cadastro.fotos FROM Netlib\PrincipalBundle\Entity\Cadastro cadastro WHERE cadastro.sexo = {$filtro} AND cadastro.apelido <> :valor AND cadastro.ativo = :ativo AND";
            else
                $sql = "SELECT cadastro.apelido, cadastro.fotos FROM Netlib\PrincipalBundle\Entity\Cadastro cadastro WHERE cadastro.apelido <> :valor AND cadastro.ativo = :ativo AND";

            foreach ($interessesFiltrados as $key => $val) {
                if ($i == 1)
                    $sql .= "(cadastro.interesses LIKE ?{$i}";
                else
                    $sql .= "OR cadastro.interesses LIKE ?{$i}";
                $i++;
            }

            $sql .= ")";

            $banco = $this->getDoctrine()->getManager();
            $query = $banco->createQuery($sql);

            $i = 1;

            foreach ($interessesFiltrados as $key => $val) {
                $query->setParameter($i, '%' . $val . '%');
                $i++;
            }
            
            $query->setParameter(':valor', $this->getRequest()->getSession()->get('login'));
            $query->setParameter(':ativo', 1);
            
            if ($this->get('request')->request->get('scroll')){
                       $query->setMaxResults($this->limite);
                       $query->setFirstResult($this->get('request')->request->get('carregar'));
            }
            else{
                $query->setMaxResults($this->limite);
            }
            
            return $query->getResult();
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao pesquisar interesses' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao pesquisar interesses');
        }
    }

    /**
     * Função para pegar o filtro de sexo, para realizar a busca dos usuarios de acordo com o interesse colocado na busca
     * @return array = retorna um array contendo o filtro do usuario logado
     */
    private function receberFiltroAction() {
        try {
            return $this->getDoctrine()->getRepository('NetlibPrincipalBundle:Cadastro')->createQueryBuilder('cadastro')
                            ->select('cadastro.filtro')
                            ->where('cadastro.apelido = :valor')
                            ->setParameter('valor', $this->getRequest()->getSession()->get('login'))
                            ->getQuery()
                            ->getSingleResult();
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao receber filtro de busca do usuário' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao receber filtro de busca do usuário');
        }
    }

    /**
     * Função para receber o id do usuário logado
     * @return array = retorna um array com o usuário logado
     */
    private function receberIdAction() {
        try {
            return $this->getDoctrine()->getRepository('NetlibPrincipalBundle:Cadastro')->createQueryBuilder('cadastro')
                            ->select('cadastro.id')
                            ->where('cadastro.apelido = :valor')
                            ->setParameter('valor', $this->getRequest()->getSession()->get('login'))
                            ->getQuery()
                            ->getSingleResult();
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao receber id do usuário logado' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao receber id do usuário logado');
        }
    }

    /**
     * Função para receber o resultado final da busca, contendo os usuarios e suas informações
     * @param array $usuarios = array contendo os ids dos usuarios da busca
     * @return array = retorna um array multidimensional com os dados do usuario da busca
     */
    private function receberDadosUsuariosBuscaAction($usuarios) {
        try {

            $query = $this->getDoctrine()->getRepository('NetlibPrincipalBundle:Cadastro')->createQueryBuilder('cadastro');
            $query->select('cadastro.apelido', 'cadastro.fotos');

            foreach ($usuarios as $key) {
                foreach ($key as $val) {
                    $valores[] = $val;
                }
            }
            return $query->where('cadastro.id IN (:valores)')->andWhere('cadastro.ativo = :valor')->setParameter('valor', 1)->setParameter('valores', $valores)->getQuery()->getResult();
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao receber usuarios da busca' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao receber usuarios da busca');
        }
    }

    /**
     * Função para pesquisar usuários que existem no site
     * @param integer $filtro = filtro do usuário logado, para efetuar a busca
     * @return array = array contendo os usuários do site
     */
    private function pesquisarUsuariosAction($filtro) {
        try {
            if ($filtro != 2) {
                return $this->getDoctrine()->getRepository('NetlibPrincipalBundle:Cadastro')->createQueryBuilder('cadastro')
                                ->select('cadastro.apelido', 'cadastro.fotos')
                                ->where('cadastro.sexo = :valor')
                                ->andWhere('cadastro.ativo = :valor2')
                                ->andWhere('cadastro.apelido <> :valor3')
                                ->setParameter('valor', $filtro)
                                ->setParameter('valor2', 1)
                                ->setParameter('valor3', $this->getRequest()->getSession()->get('login'))
                                ->setMaxResults($this->limite)
                                ->setFirstResult($this->get('request')->request->get('carregar'))
                                ->getQuery()->getResult();
            } else {
                return $this->getDoctrine()->getRepository('NetlibPrincipalBundle:Cadastro')->createQueryBuilder('cadastro')
                                ->select('cadastro.apelido', 'cadastro.fotos')
                                ->andWhere('cadastro.ativo = :valor')
                                ->andWhere('cadastro.apelido <> :valor2')
                                ->setParameter('valor', 1)
                                ->setParameter('valor2', $this->getRequest()->getSession()->get('login'))
                                ->setMaxResults($this->limite)
                                ->setFirstResult($this->get('request')->request->get('carregar'))
                                ->getQuery()->getResult();
            }
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao pesquisar usuários do site' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao pesquisar usuários do site');
        }
    }

    //---------------------------------------------MÉTODOS FAVORITOS-------------------------------------------------//

    /**
     * Função para adicionar usuário selecionado nos favoritos
     */
    private function adicionarFavoritosAction() {
        try {
            $retorno = $this->receberFavoritosAction();

            if ($retorno['favoritos']) {
                $favoritos = $retorno['favoritos'] . '|' . $this->get('request')->request->get('adicionarFavoritos');
            } else {
                $favoritos = $this->get('request')->request->get('adicionarFavoritos');
            }

            $cadastro = $this->getDoctrine()->getRepository('NetlibPrincipalBundle:Cadastro')->findOneByApelido($this->getRequest()->getSession()->get('login'));
            $cadastro->setFavoritos($favoritos);

            $banco = $this->getDoctrine()->getManager();
            $banco->persist($cadastro);
            $banco->flush();
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao adicionar usuário aos favoritos' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao adicionar usuário aos favoritos');
        }
    }

    /**
     * Função para verificar se o usuário selecionado já foi adicionado anteriormente nos favoritos
     * @return boolean = retorna true se o usuário já foi adicionado, false caso ainda não foi
     */
    private function verificarFavoritosAction() {
        try {
            $retorno = $this->receberFavoritosAction();
            $favoritos = explode('|', $retorno['favoritos']);

            foreach ($favoritos as $val) {
                if ($val == $this->get('request')->request->get('verificarFavoritos'))
                    return true;
            }

            return false;
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao verificar se usuário já existe nos favoritos' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao verificar se usuário já existe nos favoritos');
        }
    }

    /**
     * Função para receber os usuários que constam nos favoritos
     * @return array = array contendo os usuários que estão no favoritos
     */
    private function receberFavoritosAction() {
        try {
            return $this->getDoctrine()->getRepository('NetlibPrincipalBundle:Cadastro')->createQueryBuilder('cadastro')
                            ->select('cadastro.favoritos')
                            ->where('cadastro.apelido = :valor')
                            ->setParameter('valor', $this->getRequest()->getSession()->get('login'))
                            ->getQuery()
                            ->getSingleResult();
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao receber os usuários que estão nos favoritos' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao receber os usuários que estão nos favoritos');
        }
    }

    /**
     * Função para excluir usuário dos favoritos
     */
    private function excluirFavoritosAction() {
        try {
            $retorno = $this->receberFavoritosAction();
            $arrayFavoritos = explode('|', $retorno['favoritos']);

            foreach ($arrayFavoritos as $key => $val) {
                if ($val == $this->get('request')->request->get('excluirFavoritos'))
                    unset($arrayFavoritos[$key]);
            }

            $favoritos = implode('|', $arrayFavoritos);

            $cadastro = $this->getDoctrine()->getRepository('NetlibPrincipalBundle:Cadastro')->findOneByApelido($this->getRequest()->getSession()->get('login'));
            $cadastro->setFavoritos($favoritos);

            $banco = $this->getDoctrine()->getManager();
            $banco->persist($cadastro);
            $banco->flush();
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao excluir usuário dos favoritos' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao excluir usuário dos favoritos');
        }
    }

    //---------------------------------------------MÉTODOS MENSAGENS-------------------------------------------------//

    /**
     * Função para receber os dados dos contatos do chat do usuario logado
     * @param array $usuarios = array contendo os usuarios que estão na lista de contatos do chat
     * @return array = array contendo apelido, foto e status(online ou offline)
     */
    private function receberDadosContatosChatAction($usuarios) {
        try {
            $query = $this->getDoctrine()->getRepository('NetlibPrincipalBundle:Cadastro')->createQueryBuilder('cadastro');
            $query->select('cadastro.apelido', 'cadastro.fotos', 'cadastro.status');

            foreach ($usuarios as $val) {
                $valores[] = $val;
            }
            return $query->where('cadastro.apelido IN (:valores)')->setParameter('valores', $valores)->getQuery()->getResult();
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao receber dados gerais dos contatos do chat' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao receber dados gerais dos contatos do chat');
        }
    }

    /**
     * Função para receber as fotos de perfil dos contatos que estão no historico de mensagens
     * @param array $usuarios = array contendo os usuarios que estão na lista de contatos do chat
     * @return array = array contendo apelido e foto de cada usuario
     */
    private function receberFotosContatosAction($usuarios) {
        try {
            $query = $this->getDoctrine()->getRepository('NetlibPrincipalBundle:Cadastro')->createQueryBuilder('cadastro');
            $query->select('cadastro.apelido', 'cadastro.fotos');

            foreach ($usuarios as $val) {
                $valores[] = $val;
            }
            return $query->where('cadastro.apelido IN (:valores)')->setParameter('valores', $valores)->getQuery()->getResult();
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao receber fotos dos contatos da área de mensagens' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao receber fotos dos contatos da área de mensagens');
        }
    }

    /**
     * Função para receber os usuarios e seus status online ou offline
     * @param array $usuarios = arry contendo nome dos usuarios do historico do chat
     * @return array = retorna um array contendo o nome do usuario e seu status
     */
    private function receberStatusUsuariosAction($usuarios) {
        try {
            $query = $this->getDoctrine()->getRepository('NetlibPrincipalBundle:Cadastro')->createQueryBuilder('cadastro');
            $query->select('cadastro.apelido', 'cadastro.status');

            foreach ($usuarios as $val) {
                $valores[] = $val;
            }
            return $query->where('cadastro.apelido IN (:valores)')->setParameter('valores', $valores)->getQuery()->getResult();
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao receber status dos usuarios do chat' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao receber status dos usuarios do chat');
        }
    }

    /**
     * Função para atualizar o status do usuario para offline
     */
    private function offlineAction() {
        try {
            $cadastro = $this->getDoctrine()->getRepository('NetlibPrincipalBundle:Cadastro')->findOneByApelido($this->getRequest()->getSession()->get('login'));
            $cadastro->setStatus(0);
            $banco = $this->getDoctrine()->getManager();
            $banco->persist($cadastro);
            $banco->flush();
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao atualizar status do usuario para offline' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao atualizar status do usuario para offline');
        }
    }

}
