function Mensagem() {
    var obj; //propriedade para definir os dados do objeto, que seram enviados para o ajax
    var options; //propriedade que recebe os valores de configuração do ajax
    var html; //propriedade que armazena o conteudo que será inserido no html
    var i = 0; //propriedade para controle de loop
    var j = 0; //propriedade para controle de loop
    var pos; //propriedade de posicao para carregar a busca a partir da mensagem X do arquivo de conversa de acordo com o limite de mensagens por pagina
    var chat; //propriedade que armazena o usuario atual da conversa no chat
    var quantidadeMensagensChat; //quantidade de mensagens no chat com a pessoa selecionada 
    //var perfil = new Perfil; //instacia a classe Pefil

    /**
     * Função para exibir lista de historico de conversar e abrir janela de chat com o usuario selecionado
     * @param {string} usuario = usuario selecionado para exibir historico de conversa
     * @returns obj = retorna obj com historico de conversa, fotos dos usuarios, nomes, conversa
     */
    this.mostrarChat = function(usuario) {
        chat = usuario;
        notificacao = false;
        excluirNotificacao(usuario);
        $('#removerFoto1').css('display', 'none'); //linha para corrigir bug de aparecer o botao, sendo que a tela foi fechada
        $('#removerFoto2').css('display', 'none'); //linha para corrigir bug de aparecer o botao, sendo que a tela foi fechada
        $('#removerFoto3').css('display', 'none'); //linha para corrigir bug de aparecer o botao, sendo que a tela foi fechada
        $('#notificacao').attr('class', 'new-message-poupup-container down');
        $('#usuarioConversa').empty();
        $('.chat-messages-area').empty();
        $('.chat-contacts').empty();
        $('.chat-messages').css('display', 'block');
        $('#usuarioConversa').css('display', 'block');
        $('#formulario_mensagem input').val('');
        $('#mensagens').css('display', 'block');
        $('#formulario_mensagem').css('display', 'block');
        obj = {chat: usuario};
        ajaxPadrao('POST', obj, '', 'json', true, 'Erro ao mostrar as mensagens com o úsuario selecionado', function(retorno) {
            if (retorno) {
                $('.chat-contacts').html('<button class="contacts-viewport-button">ver mensagens</button>');
                quantidadeMensagensChat = retorno.quantidadeMensagensChat;
                var fotos = retorno.perfil.fotos.split('|');
                if (retorno.limite)
                    pos = retorno.limite;

                for (i in fotos) {
                    if (fotos[i].substring(0, 2) == 'PE') {
                        $('#usuarioConversa').html('<section><figure class="chat-contact"><img src=\'sistema/albuns/' + usuario + '/' + fotos[i] + '\' alt=\'' + retorno.perfil.apelido + '\' onclick=perfis.mostrarPerfil($(this).attr(\'alt\')) style="cursor:pointer"><span></span><figcaption>' + retorno.perfil.apelido + '</figcaption></figure></section>');
                    }
                }

                if (retorno.conversa) {
                    for (i in retorno.conversa.mensagem) {
                        if (retorno.conversa.enviou[i] != retorno.login)
                            $('.chat-messages-area').append('<section class="recieved"><p>' + retorno.conversa.enviou[i] + ' ' + retorno.conversa.data[i] + ' ' + retorno.conversa.hora[i] + ' - ' + retorno.conversa.mensagem[i] + '</p></section>');
                        else
                            $('.chat-messages-area').append('<section class="sent"><p>' + retorno.conversa.enviou[i] + ' ' + retorno.conversa.data[i] + ' ' + retorno.conversa.hora[i] + ' - ' + retorno.conversa.mensagem[i] + '</p></section>');
                    }

                    if (!retorno.termino)
                        $('.chat-messages-area').append('<section class="recieved" id=\'mensagensHistorico\' onclick=mensagem.carregarHistorico("' + usuario + '")><p>Ver mais</p></section>');
                }

                if (retorno.contatos) {
                    for (i in retorno.contatos) {
                        var val = retorno.contatos[i].fotos.split('|');
                        for (j in val) {
                            if (val[j].substring(0, 2) == 'PE') {
                                if (retorno.contatos[i].status == 0)
                                    $('.chat-contacts').append('<section class="chat-contact" alt=\'' + retorno.contatos[i].apelido + '\'><figure alt=\'' + retorno.contatos[i].apelido + '\' onclick=mensagem.mostrarChat($(this).attr(\'alt\'))><img src=\'sistema/albuns/' + retorno.contatos[i].apelido + '/' + val[j] + '\' alt=\'' + retorno.contatos[i].apelido + '\' onclick=mensagem.mostrarChat($(this).attr(\'alt\'))><span class="offline"></span> <figcaption>' + retorno.contatos[i].apelido + '</figcaption></figure><button class="chat-delete-button" aria-label="Limpar histórico deste contato" data-icon="&#61526;" type="submit" value="Limpar" onclick=mensagem.limparHistorico($(this).attr(\'alt\')) alt=\'' + retorno.contatos[i].apelido + '\'></button></section>');
                                else
                                    $('.chat-contacts').append('<section class="chat-contact" alt=\'' + retorno.contatos[i].apelido + '\'><figure alt=\'' + retorno.contatos[i].apelido + '\' onclick=mensagem.mostrarChat($(this).attr(\'alt\'))><img src=\'sistema/albuns/' + retorno.contatos[i].apelido + '/' + val[j] + '\' alt=\'' + retorno.contatos[i].apelido + '\' onclick=mensagem.mostrarChat($(this).attr(\'alt\'))><span class="online"></span> <figcaption>' + retorno.contatos[i].apelido + '</figcaption></figure><button class="chat-delete-button" aria-label="Limpar histórico deste contato" data-icon="&#61526;" type="submit" value="Limpar" onclick=mensagem.limparHistorico($(this).attr(\'alt\')) alt=\'' + retorno.contatos[i].apelido + '\'></button></section>');
                            }
                        }
                    }

                    $('.chat-contacts section').each(function() {
                        if (retorno.notificacao) {
                            for (i in retorno.notificacao.usuario) {
                                if (retorno.notificacao.usuario[i] == $(this).attr('alt'))
                                    $(this).addClass('flicking');
                            }
                        }
                    });
                } else {
                    for (i in fotos) {
                        if (fotos[i].substring(0, 2) == 'PE')
                            $('.chat-contacts').append('<section class="chat-contact"  alt=\'' + retorno.perfil.apelido + '\' ><figure alt=\'' + retorno.perfil.apelido + '\' onclick=mensagem.mostrarChat($(this).attr(\'alt\'))><img src=\'sistema/albuns/' + retorno.perfil.apelido + '/' + fotos[i] + '\' alt=\'' + retorno.perfil.apelido + '\' onclick=mensagem.mostrarChat($(this).attr(\'alt\'))><span class="offline"></span> <figcaption>' + retorno.perfil.apelido + '</figcaption></figure><button class="chat-delete-button" aria-label="Limpar histórico deste contato" data-icon="&#61526;" type="submit" value="Limpar" onclick=mensagem.limparHistorico($(this).attr(\'alt\')) alt=\'' + retorno.perfil.apelido + '\'></button></section>');
                    }
                }
            }
        });

        $('#alerts').attr('class', 'alerts-off');
        $('#alerts').html("<p id=\"mensagemAviso\"></p><p id=\"mensagemApelido\"></p><p id=\"mensagemEmail\"></p><p id=\"aviso\"></p><p id=\"erros\"></p>");
        $('.profile').attr('id', 'empty-article');
        $('.userprofile').attr('id', 'empty-article');
        $('.chat-messages-viewport').attr('id', 'fill-article');
    };

    /**
     * Função para enviar mensagem ao usuario selecionado
     * @returns obj = retorna a mensagem enviada para mostrar na tela
     */
    this.enviarMensagem = function() {
        obj = {mensagemDestinatario: chat};
        options = configAjaxForm('POST', obj, '', 'json', true, 'Erro no envio de mensagem', function(retorno) {
            if (retorno) {
                if (retorno.capacidadeEsgotada) {
                    $(function() {
                        $("<div>Seu histórico está lotado! Esvazie para enviar uma mensagem para um novo usuário!</div>").dialog({
                            autoOpen: true,
                            title: 'Mensagem',
                            buttons: {
                                "OK": function() {
                                    $(this).dialog("close");
                                }
                            },
                            height: "auto",
                            width: "auto"
                        });
                    });
                } else if (retorno.erros) {
                    $(function() {
                        $("<div>Você tentou enviar uma mensagem em branco ou ultrapassou o número de caracteres permitidos</div>").dialog({
                            autoOpen: true,
                            title: 'Mensagem',
                            buttons: {
                                "OK": function() {
                                    $(this).dialog("close");
                                }
                            },
                            height: "auto",
                            width: "auto"
                        });
                    });
                } else {
                    $('.chat-messages-area').prepend('<section class="sent"><p>' + retorno.enviou + ' ' + retorno.data + ' ' + retorno.hora + ' - ' + retorno.mensagem + '</p></section>');
                    $('#mensagem_mensagem').val('');
                }
            } else {
                $('#mensagens').prepend('<p style="color:red">Erro ao enviar mensagem</p>');
                $('#mensagem_mensagem').val('');
            }
        });
        $('#formulario_mensagem').ajaxForm(options);
    };

    /**
     * Função para verificar recebimento de novas mensagens e atualizar mensagens recebidas no chat e da atual conversa
     * @param {boolean} telaMensagens = true o usuario está na tela de mensagens para chat, false o usaurio não está na área de mensagens
     * @returns {obj} = retorna objeto com os usuarios que enviaram mensagem para o usuario logado
     */
    this.receberNotificacao = function(telaMensagens) {
        if (!telaMensagens) {
            obj = {notificacao: true};
            ajaxPadrao('POST', obj, '', 'json', false, 'Erro ao receber notificacoes do usuario', function(retorno) {
                if (retorno) {
                    $('#notificacao').attr('class', 'new-message-poupup-container');

                    //condicao para evitar bug de aparecer janela de nova mensagem, com a tela de mensagens aberta
                    if ($('.chat-contacts section').length != 0)
                        $('#notificacao').attr('class', 'new-message-poupup-container down');

                    notificacao = true;
                }

            });
        } else if (telaMensagens) {
            obj = {notificacao: true};
            ajaxPadrao('POST', obj, '', 'json', false, 'Erro ao receber notificacoes do usuario', function(retorno) {
                if (retorno) {
                    $('.chat-contacts section').each(function() {
                        for (i in retorno.usuario) {
                            if (retorno.usuario[i] == $(this).attr('alt') && retorno.usuario[i] != chat) {
                                $(this).addClass('flicking');
                            } else if (retorno.usuario[i] == chat && quantidadeMensagensChat && chat == $(this).attr('alt')) {
                                obj = {notificacao: true, mensagensChat: quantidadeMensagensChat, chat: chat};
                                ajaxPadrao('POST', obj, '', 'json', false, 'Erro ao receber notificacoes do usuario atual do chat', function(retorno) {
                                    if (retorno) {
                                        quantidadeMensagensChat = retorno.quantidadeMensagensChatAtual;

                                        for (i in retorno.conversa.mensagem)
                                            $('.chat-messages-area').prepend('<section class="recieved"><p>' + retorno.conversa.enviou + ' ' + retorno.conversa.data + ' ' + retorno.conversa.hora + ' - ' + retorno.conversa.mensagem + '</p></section>');


                                    }
                                });
                                excluirNotificacao(chat);
                            }
                        }
                    });
                }
            });
        }
    };

    /**
     * Função para excluir notificacao de mensagem do usuario que foi clicado, ou seja já foi visualizada
     * @param {string} usuario = usuario clicado n oqual visualizou a mensagem
     * @returns {true}
     */
    function excluirNotificacao(usuario) {
        obj = {'limparNotificacao': usuario};
        ajaxPadrao('POST', obj, '', 'json', false, 'Erro ao receber limpar notificacao do usuário clicado', true);
    }

    /**
     * Função para carregar historico de conversa de um usuario selecionado
     * @param {string} usuario = usuario que eu quero receber historico de conversa
     * @returns {obj} = retorna um objeto com as mensagens com aquele usuario selecionado
     */
    this.carregarHistorico = function(usuario) {
        $(this).removeClass('flicking');
        $('#mensagensHistorico').remove();
        obj = {carregarHistorico: usuario, posicao: pos};
        ajaxPadrao('POST', obj, '', 'json', true, 'Erro ao mostrar historico do usuario selecionado', function(retorno) {
            if (retorno) {
                pos = retorno.limite + pos;

                for (i in retorno.conversa.mensagem) {
                    if (retorno.conversa.enviou[i] != retorno.login)
                        $('.chat-messages-area').append('<section class="recieved"><p>' + retorno.conversa.enviou[i] + ' ' + retorno.conversa.data[i] + ' ' + retorno.conversa.hora[i] + ' - ' + retorno.conversa.mensagem[i] + '</p></section>');
                    else
                        $('.chat-messages-area').append('<section class="sent"><p>' + retorno.conversa.enviou[i] + ' ' + retorno.conversa.data[i] + ' ' + retorno.conversa.hora[i] + ' - ' + retorno.conversa.mensagem[i] + '</p></section>');
                }

                if (!retorno.termino)
                    $('.chat-messages-area').append('<section class="recieved" id=\'mensagensHistorico\' onclick=mensagem.carregarHistorico("' + usuario + '")><p>Ver mais</p></section>');
            }
        });
    };

    /**
     * Função para limpar historico de conversa do usuario selecionado
     * @param {string} usuario = usuario selecionado ao qual vai ter o historico de conversa apagado
     * @returns {boolean} = true foi apagado com sucesso
     */
    this.limparHistorico = function(usuario) {
        excluirNotificacao(usuario);
        obj = {limparHistorico: usuario};
        ajaxPadrao('POST', obj, '', 'json', true, 'Erro ao limpar historico de mensagem', function(retorno) {
            if (retorno) {
                $('.chat-contacts section').each(function() {
                    if ($(this).attr('alt') == usuario)
                        $(this).remove();
                });
            }

            $('.chat-contact img').each(function() {
                if ($(this).attr('alt') == usuario) {
                    $('.chat-messages').css('display', 'none');
                    $('#usuarioConversa').css('display', 'none');
                    $('#formulario_mensagem input').val('');
                    $('#mensagens').css('display', 'none');
                }
            });

        });
    };


    /**
     * Função para receber os contatos e atualizar com os novos contatos do chat do usuario logado, caso se houver
     * @returns {obj} = contendo os contatos com nome e foto
     */
    this.receberContatos = function() {
        obj = {receberContatos: true};
        ajaxPadrao('POST', obj, '', 'json', false, 'Erro ao receber contatos do chat', function(retorno) {
            if (retorno.contatos) {
                $('.chat-contacts section').each(function() {
                    for (i in retorno.contatos) {
                        if (retorno.contatos[i].apelido == $(this).attr('alt'))
                            delete retorno.contatos[i];
                    }
                });

                for (i in retorno.contatos) {
                    var val = retorno.contatos[i].fotos.split('|');
                    for (j in val) {
                        if (val[j].substring(0, 2) == 'PE')
                            $('.chat-contacts').append('<section class="chat-contact"  alt=\'' + retorno.contatos[i].apelido + '\'><figure alt=\'' + retorno.contatos[i].apelido + '\' onclick=mensagem.mostrarChat($(this).attr(\'alt\'))><img src=\'sistema/albuns/' + retorno.contatos[i].apelido + '/' + val[j] + '\' alt=\'' + retorno.contatos[i].apelido + '\' onclick=mensagem.mostrarChat($(this).attr(\'alt\'))><span class="online"></span> <figcaption>' + retorno.contatos[i].apelido + '</figcaption></figure><button class="chat-delete-button" aria-label="Limpar histórico deste contato" data-icon="&#61526;" type="submit" value="Limpar" onclick=mensagem.limparHistorico($(this).attr(\'alt\')) alt=\'' + retorno.contatos[i].apelido + '\'></button></section>');
                    }
                }
            }
        });
    };

    /**
     * Função para verificar status dos contatos do chat, verificar quem está online e offline
     * @returns {obj} = contendo os usuarios que estão online
     */
    this.verificarStatus = function() {
        var controle = 0;
        var arrayContatos = [];

        $('.chat-contacts section').each(function() {
            arrayContatos[controle] = $(this).attr('alt');
            controle++;
        });

        obj = {receberStatus: arrayContatos};
        ajaxPadrao('POST', obj, '', 'json', false, 'Erro ao receber status dos usuarios do chat', function(retorno) {
            if (retorno) {
                $('.chat-contacts section').each(function() {
                    for (i in retorno.usuarioStatus) {
                        if (retorno.usuarioStatus[i].apelido == $(this).attr('alt') && retorno.usuarioStatus[i].status == 1)
                            $(this).find('span').attr('class', 'online');
                        else if (retorno.usuarioStatus[i].apelido == $(this).attr('alt') && retorno.usuarioStatus[i].status == 0)
                            $(this).find('span').attr('class', 'offline');
                    }
                });
            }
        });
    };
}

