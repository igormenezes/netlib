<?php

namespace Netlib\PrincipalBundle\Manager;

class MensagemManager {

    private $usuarioLogado; //propriedade que armazena o nome do usuario logado
    private $arquivo; //propriedade que armazena o arquivo de conversa do usuario logado
    private $backup; //propriedade que armazena o arquivo de backup da conversa do usuario logado
    private $status = "sistema/online.json"; //propriedade que armazena um bojeto com os usuarios online no sistema
    private $notificacao = "sistema/notificacao/"; //propriedade que armazena o arquivo de notificacao do usuario
    private $limite = 10; //propriedade que armazena o limite de mensagens do historico por carregamento
    private $contatos = 30; //propriedade que armazena o numero maximo de contatos no chat por usuario

    /**
     * Função mágica construtora, executada para determinar o valor da propriedade arquivo e backup, para que elas não tenham que se repetir dentro da classe
     * @param type $login = usuario logado
     */

    public function __construct($login) {
        $this->usuarioLogado = $login;
        $this->arquivo = "sistema/chat/{$login}.json";
        $this->backup = "sistema/backupChat/{$login}.json";
    }

    /**
     * Função para enviar a mensagem para o outro usuario, 
     * Salva em um arquivo json o historico de conversa, tanto no arquivo json do remetente, quanto do destinatario
     * Alem de salvar um backup da conversa
     * @param string $mensagem = mensagem enviada para destinatario
     * @param string $destinatario = usuario que vai receber a mensagem
     * $param boolean $backup = true salva a mensagem no arquivo de backup, false salva a mensagem no arquivo de mensagem padrao
     * @param boolean $salvarDestinatario = false eu envio a mensagem e salvo o arquivo json do usuario logado (remetente), true eu envio a mensagem  e salvo o arquivo json do usuario destinatario 
     * esse parametro é necessario também para quando for salvar a mensagem no remetente o usuario ser o destinatario e quando salvar no destinatario o usuario ser o remetente(para nao ter bug do usuario receber mensagem dele mesmo)
     * @return json = retorna um json, com a mensagem que foi enviada ao usuario
     */
    public function salvarMensagem($mensagem, $destinatario, $backup = false, $salvarDestinatario = false) {
        try {
            $capacidadeEsgotada = $this->verificarLimiteContatos($destinatario);

            if ($capacidadeEsgotada)
                return false;

            if (!$backup) {
                $arquivo = $salvarDestinatario ? "sistema/chat/{$destinatario}.json" : $this->arquivo;
                $usuario = !$salvarDestinatario ? $destinatario : $this->usuarioLogado;
            } else {
                $arquivo = $salvarDestinatario ? "sistema/backupChat/{$destinatario}.json" : $this->backup;
                $usuario = !$salvarDestinatario ? $destinatario : $this->usuarioLogado;
            }

            if (!file_exists($arquivo)) {
                $array['conversa'][0]['usuario'] = base64_encode($usuario);
                $array['conversa'][0]['enviou'][0] = base64_encode($this->usuarioLogado);
                $array['conversa'][0]['data'][0] = base64_encode(date('d-m-Y'));
                $array['conversa'][0]['hora'][0] = base64_encode(date('H:i'));
                $array['conversa'][0]['mensagem'][0] = base64_encode($mensagem);
            } else {
                $json = fopen($arquivo, 'r');
                $conteudo = fread($json, filesize($arquivo));
                fclose($json);

                $array = json_decode($conteudo, true);
                $i = 0;

                foreach ($array['conversa'] as $valores) {
                    if (base64_decode($valores['usuario']) == $usuario) {
                        $conversa = true;
                        $proximo = count($valores['mensagem']);

                        $array['conversa'][$i]['enviou'][$proximo] = base64_encode($this->usuarioLogado);
                        $array['conversa'][$i]['data'][$proximo] = base64_encode(date('d-m-Y'));
                        $array['conversa'][$i]['hora'][$proximo] = base64_encode(date('H:i'));
                        $array['conversa'][$i]['mensagem'][$proximo] = base64_encode($mensagem);
                        break;
                    }
                    $i++;
                }

                if (!isset($conversa)) {
                    $array['conversa'][$i]['usuario'] = base64_encode($usuario);
                    $array['conversa'][$i]['enviou'][0] = base64_encode($this->usuarioLogado);
                    $array['conversa'][$i]['data'][0] = base64_encode(date('d-m-Y'));
                    $array['conversa'][$i]['hora'][0] = base64_encode(date('H:i'));
                    $array['conversa'][$i]['mensagem'][0] = base64_encode($mensagem);
                }
            }

            $json = json_encode($array);
            file_put_contents($arquivo, $json);

            if (!$backup && !$salvarDestinatario)
                $this->salvarNotificacao($destinatario);

            if (!$backup) {
                $dados['usuario'] = $usuario;
                $dados['enviou'] = $this->usuarioLogado;
                $dados['data'] = date('d-m-Y');
                $dados['hora'] = date('H:i');
                $dados['mensagem'] = $mensagem;
                return $dados;
            }
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao enviar mensagem' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao enviar mensagem');
        }
    }

    /**
     * Função para receber o historico de mensagens da conversa do usuario logado e o seu destinatario
     * E receber uma lista com historico de outras conversas
     * @param string $usuarioConversa = usuario da conversa atual
     * @return json = retorna um json com as mensagens em historico e da atual conversa
     */
    public function receberMensagens($usuarioConversa = false) {
        try {
            $array = $this->lerArquivo();

            if (!$array)
                return false;

            if ($usuarioConversa) {
                $j = 0;
                foreach ($array['conversa'] as $valores) {
                    if (base64_decode($valores['usuario']) == $usuarioConversa) {
                        $dados['quantidadeMensagensChat'] = $quantidadeMensagens = count($valores['mensagem']);
                        $pos = count($valores['mensagem']) - 1; //pegar as mensagens da ultima para a primeira em ordem decrescente

                        foreach ($valores['mensagem'] as $val) {
                            if ($j == $this->limite) {
                                $dados['limite'] = $this->limite;
                                $dados['termino'] = ($j == ($quantidadeMensagens)) ? true : false;
                                break;
                            }

                            $dados['conversa']['enviou'][$j] = base64_decode($valores['enviou'][$pos]);
                            $dados['conversa']['data'][$j] = base64_decode($valores['data'][$pos]);
                            $dados['conversa']['hora'][$j] = base64_decode($valores['hora'][$pos]);
                            $dados['conversa']['mensagem'][$j] = base64_decode($valores['mensagem'][$pos]);
                            $j++;
                            $pos--;
                        }
                    }
                    $dados['contatos'][] = base64_decode($valores['usuario']);
                }
            } else {
                foreach ($array['conversa'] as $valores) {
                    $dados['contatos'][] = base64_decode($valores['usuario']);
                }
            }
            return $dados;
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao receber mensagens' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao receber mensagens');
        }
    }

    /**
     * FunÃ§Ã£o para receber as mensagens recebidas do usuario atual da conversa e atualizar o chat
     * @param string $usuario = usuario com quem estÃ¡ mantendo conversa
     * @param string $quantidadeMensagensChat = quantidade de mensagens do chat atÃ© o momento antes de atualizar com os novos dados
     * @return array = retorna array com dados das ultimas mensagens da conversa
     */
    public function receberMensagensChat($usuario, $quantidadeMensagensChat) {
        try {
            $array = $this->lerArquivo();
            foreach ($array['conversa'] as $valores) {
                $i = 0;

                if (base64_decode($valores['usuario']) == $usuario) {

                    $quantidadeMensagensChatAtual = count($valores['mensagem']);

                    $pos = $quantidadeMensagensChatAtual - 1;
                    $novaMensagem = ($quantidadeMensagensChatAtual - $quantidadeMensagensChat) - 1;

                    for ($j = 0; $novaMensagem >= 0; $j++) {
                        if (base64_decode($valores['enviou'][$pos]) == $usuario) {
                            $dados['quantidadeMensagensChatAtual'] = $quantidadeMensagensChatAtual;
                            $dados['conversa']['enviou'][$j] = base64_decode($valores['enviou'][$pos]);
                            $dados['conversa']['data'][$j] = base64_decode($valores['data'][$pos]);
                            $dados['conversa']['hora'][$j] = base64_decode($valores['hora'][$pos]);
                            $dados['conversa']['mensagem'][$j] = base64_decode($valores['mensagem'][$pos]);
                        }

                        $pos--;
                        $novaMensagem--;
                    }
                }
                $i++;
            }

            if (isset($dados))
                return $dados;
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao receber mensagens do usuario atual do chat' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao receber mensagens do usuario atual do chat');
        }
    }

    /**
     * Função para salvar notificacao no arquivo do destinatari, salva o nome de quem enviou a mensagem
     * @param string $destinatario = usuario que irá receber a mensagem enviada
     */
    private function salvarNotificacao($destinatario) {
        try {
            $arquivo = "$this->notificacao{$destinatario}.json";

            if (!file_exists($arquivo)) {
                $array['usuario'][0] = base64_encode($this->usuarioLogado);
                $json = json_encode($array);
                file_put_contents($arquivo, $json);
            } else {
                $array = $this->lerArquivo($arquivo);
                $controle = true;
                $proximo = count($array['usuario']);
                foreach ($array['usuario'] as $val) {
                    if (base64_decode($val) == $this->usuarioLogado)
                        $controle = false;
                }

                if ($controle) {
                    $array['usuario'][$proximo] = base64_encode($this->usuarioLogado);
                    $json = json_encode($array);
                    file_put_contents($arquivo, $json);
                }
            }
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao salvar notificação' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao salvar notificação');
        }
    }

    /**
     * Função que recebe as notificacoes, as novas mensagens recebidas e atualiza lista de contatos do chat
     * @return array = retorna um array com os nomes dos usuarios que enviaram as novas mensagens para o usuario logado
     */
    public function receberNotificacoes() {
        try {
            $arquivo = "$this->notificacao{$this->usuarioLogado}.json";

            if (file_exists($arquivo)) {
                $array = $this->lerArquivo($arquivo);

                foreach ($array['usuario'] as $val)
                    $dados['usuario'][] = base64_decode($val);

                return $dados;
            }
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao receber notificacoes' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao receber notificacoes');
        }
    }

    public function receberContatos() {
        try {
            $array = $this->lerArquivo();

            foreach ($array['conversa'] as $valores)
                $dados['contatos'][] = base64_decode($valores['usuario']);

            return $dados;
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao receber os contatos do chat' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao receber os contatos do chat');
        }
    }

    /**
     * Função para apagar notificacao de nova mensagem do usuario clicado, pois foi visualizada
     * @param string $usuario = usuario clicado, no qual foi visualizada a mensagem recebida
     */
    public function apagarNotificacao($usuario) {
        try {
            $arquivo = "$this->notificacao{$this->usuarioLogado}.json";

            if (file_exists($arquivo)) {
                $array = $this->lerArquivo($arquivo);

                foreach ($array['usuario'] as $val) {
                    if ($usuario != base64_decode($val))
                        $dados['usuario'][] = $val;
                }

                if (isset($dados)) {
                    $json = json_encode($dados);
                    file_put_contents($arquivo, $json);
                } else {
                    unlink($arquivo);
                }
            }
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao apagar a notificação' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao apagar a notificação');
        }
    }

    /**
     * Função para carregar historico de conversa com o usuario selecionado
     * @param string $usuario = usuario selecionado, no qual irá buscar dados da conversa
     * @param integer $pos = parametro de controle, para pegar a ultima posicao para carregar o restante das mensagens
     * @return array = retorna um array com as conversas
     */
    public function carregarHistorico($usuario, $pos) {
        try {
            $array = $this->lerArquivo();

            if (!$array)
                return false;

            $i = 0;
            $j = 0;

            foreach ($array['conversa'] as $valores) {
                if (base64_decode($array['conversa'][$i]['usuario']) == $usuario) {
                    $quantidadeMensagens = count($array['conversa'][$i]['mensagem']);
                    $controle = (count($valores['mensagem']) - 1 ) - $pos; //pegar as mensagens da ultima para a primeira em ordem decrescente

                    foreach ($array['conversa'][$i]['mensagem'] as $val) {
                        if ($j == $this->limite || $pos == $quantidadeMensagens) {
                            $dados['limite'] = $this->limite;
                            $dados['termino'] = ($pos == ($quantidadeMensagens)) ? true : false;
                            break;
                        }

                        $dados['conversa']['enviou'][$j] = base64_decode($array['conversa'][$i]['enviou'][$controle]);
                        $dados['conversa']['data'][$j] = base64_decode($array['conversa'][$i]['data'][$controle]);
                        $dados['conversa']['hora'][$j] = base64_decode($array['conversa'][$i]['hora'][$controle]);
                        $dados['conversa']['mensagem'][$j] = base64_decode($array['conversa'][$i]['mensagem'][$controle]);

                        $j++;
                        $pos++;
                        $controle--;
                    }
                }
                $dados['contatos'][$i] = base64_decode($array['conversa'][$i]['usuario']);
                $i++;
            }
            return $dados;
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao carregar historico de mensagens' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao carregar historico de mensagens');
        }
    }

    /**
     * Função para apagar o historico de conversa do usuario logado com o usuario selecionado (apaga apenas o arquivo json do usuario logado (remetente))
     * @param string $usuarioExcluir = usuario que vai ser apagado do historico de conversas do usuario logado
     */
    public function limparHistorico($usuarioExcluir) {
        try {
            if (file_exists($this->arquivo)) {
                $json = fopen($this->arquivo, 'r');
                $conteudo = fread($json, filesize($this->arquivo));
                fclose($json);

                $array = json_decode($conteudo, true);
                $qtMensagens = count($array['conversa']);

                if ($qtMensagens == 1) {
                    unlink($this->arquivo);
                } else {
                    $i = 0;

                    foreach ($array['conversa'] as $valores) {
                        if (base64_decode($array['conversa'][$i]['usuario']) == $usuarioExcluir) {
                            unset($array['conversa'][$i]);
                            $array['conversa'] = array_values($array['conversa']);
                            break;
                        }
                        $i++;
                    }

                    $json = json_encode($array);
                    file_put_contents($this->arquivo, $json);
                }
            }
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao carregar limpar historico de mensagens' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao carregar limpar historico de mensagens');
        }
    }

    /**
     * Função para pegar as informacoes do usuario selecionado dentro de um arquivo json(mensagens, notificacao)
     * @param string $notificacao = caso existir, será o arquivo a ser lido
     * @return array = array contendo informacoes do arquivo lido
     */
    private function lerArquivo($notificacao = false) {
        try {
            if ($notificacao) {
                $json = fopen($notificacao, 'r');
                $conteudo = fread($json, filesize($notificacao));
                $array = json_decode($conteudo, true);
                fclose($json);
                return $array;
            } else if (file_exists($this->arquivo)) {
                $json = fopen($this->arquivo, 'r');
                $conteudo = fread($json, filesize($this->arquivo));
                $array = json_decode($conteudo, true);
                fclose($json);
                return $array;
            }
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao ler arquivo' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao ler arquivo');
        }
    }

    /**
     * Função para verificar se o limite de historico de mensagens foi atingido
     * @param string $destinatario = quem irá receber a mensagem
     * @return boolean = true historico está lotado, false historico está disponivel armazenamento
     */
    private function verificarLimiteContatos($destinatario) {
        try {
            $array = $this->lerArquivo();
            if (!$array)
                return false;

            $i = 0;
            $controle = 0;

            foreach ($array['conversa'] as $valores) {
                if (base64_decode($valores['usuario']) != $destinatario)
                    $controle++;

                $i++;
            }

            if ($controle >= $this->contatos)
                return true;
            else
                return false;
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao verificar limite de contatos no chat' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao verificar limite de contatos no chat');
        }
    }

}
