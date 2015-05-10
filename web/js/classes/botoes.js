function Botoes() {
    var obj; //propriedade para definir os dados do objeto, que seram enviados para o ajax
    var i = 0; //propriedade para controle de loop
    var j = 0; //propriedade para controle de loop

    /**
     * Função que executa o evento do botao Perfil
     * @return obj = retorna os dados do perfil do usuário
     */
    this.botaoPerfil = function() {
        $('#formulario_perfil input').val('');
        $('#formulario_perfil img').removeAttr('src');
        $('#formulario_perfil option').removeAttr('selected');
        $('#foto1').replaceWith('<img id=\'foto1\' class="img-fade" src="images/gal/tmb.jpg" alt="ProfileThumb" />'); //usando replace, em vez do attr, para evitar bug no Chrome
        $('#foto2').replaceWith('<img id=\'foto2\' class="img-fade" src="images/gal/tmb.jpg" alt="ProfileThumb" />'); //usando replace, em vez do attr, para evitar bug no Chrome
        $('#foto3').replaceWith('<img id=\'foto3\' class="img-fade" src="images/gal/tmb.jpg" alt="ProfileThumb" />'); //usando replace, em vez do attr, para evitar bug no Chrome
        $('#removerFoto1').css('display', 'none');
        $('#removerFoto2').css('display', 'none');
        $('#removerFoto3').css('display', 'none');
        obj = {'botaoPerfil': true};
        ajaxPadrao('POST', obj, '', 'json', true, 'Erro ao trazer dados do perfil para edição', function(retorno) {
            $("#apelidoPerfil").html(retorno.apelido);
            var arrayFotos = retorno.fotos.split('|');
            for (i in arrayFotos) {
                var valor = arrayFotos[i].substr(0, 2);
                switch (valor) {
                    case 'PE':
                        $('#perfil').attr('src', 'sistema/albuns/' + retorno.apelido + '/' + arrayFotos[i]);
                        break;
                    case 'F1':
                        $('#foto1').attr('src', 'sistema/albuns/' + retorno.apelido + '/' + arrayFotos[i]);
                        $('#removerFoto1').css('display', 'block');
                        break;
                    case 'F2':
                        $('#foto2').attr('src', 'sistema/albuns/' + retorno.apelido + '/' + arrayFotos[i]);
                        $('#removerFoto2').css('display', 'block');
                        break;
                    case 'F3':
                        $('#foto3').attr('src', 'sistema/albuns/' + retorno.apelido + '/' + arrayFotos[i]);
                        $('#removerFoto3').css('display', 'block');
                        break;
                }
            }

            $('#perfil_interesses').val(retorno.interesses);
            switch (retorno.filtro) {
                case 0:
                    //homens
                    $('#perfil_filtro option').each(function() {
                        if ($(this).val() == 0) {
                            $(this).attr('selected', true);
                            $('#perfil_filtro').html($('#perfil_filtro').children().clone());
                        }
                    });
                    break;
                case 1:
                    //mulheres
                    $('#perfil_filtro option').each(function() {
                        if ($(this).val() == 1) {
                            $(this).attr('selected', true);
                            $('#perfil_filtro').html($('#perfil_filtro').children().clone());
                        }
                    });
                    break;
                case 2:
                    //ambos
                    $('#perfil_filtro option').each(function() {
                        if ($(this).val() == 2) {
                            $(this).attr('selected', true);
                            $('#perfil_filtro').html($('#perfil_filtro').children().clone());
                        }
                    });
                    break;
            }
        });

        $('#alerts').attr('class', 'alerts-off');
        $('#alerts').html("<p id=\"mensagemAviso\"></p><p id=\"mensagemApelido\"></p><p id=\"mensagemEmail\"></p><p id=\"aviso\"></p><p id=\"erros\"></p>");
        $('.profile').attr('id', 'empty-article');
        $('.chat-messages-viewport').attr('id', 'empty-article');
        $('.userprofile').attr('id', 'fill-article');
    };

    /**
     * Função que executa o evento do botão favoritos
     * @returns obj = retorna os usuários que estão no favoritos do usuário logado
     */
    this.botaoFavoritos = function() {
        favoritos.receberFavoritos();
    };

    /**
     * Função que executa o evento do botão mensagens
     * @returns obj = retorna o historico de mensagens do usuario
     */
    this.botaoMensagem = function() {
        notificacao = false;
        $('#removerFoto1').css('display', 'none'); //linha para corrigir bug de aparecer o botao, sendo que a tela foi fechada
        $('#removerFoto2').css('display', 'none'); //linha para corrigir bug de aparecer o botao, sendo que a tela foi fechada
        $('#removerFoto3').css('display', 'none'); //linha para corrigir bug de aparecer o botao, sendo que a tela foi fechada
        $('#notificacao').attr('class', 'new-message-poupup-container down');
        $('#usuarioConversa').empty();
        $('.chat-messages-area').empty();
        $('.chat-contacts').empty();
        $('.chat-messages').css('display', 'none');
        $('#usuarioConversa').css('display', 'none');
        $('#formulario_mensagem').css('display', 'none');
        $('#mensagens').css('display', 'none');
        $('#alerts').attr('class', 'alerts-off');
        $('#alerts').html("<p id=\"mensagemAviso\"></p><p id=\"mensagemApelido\"></p><p id=\"mensagemEmail\"></p><p id=\"aviso\"></p><p id=\"erros\"></p>");
        obj = {botaoMensagem: true};
        ajaxPadrao('POST', obj, '', 'json', true, 'Erro ao mostrar as mensagens', function(retorno) {
            if (retorno) {
                $('.chat-messages-viewport').attr('id', 'fill-article');
                $('.chat-contacts').html('<button class="contacts-viewport-button">ver mensagens</button>');

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
                $('#alerts').attr('class', 'alerts-on');
                $('#alerts').html("<p id=\"mensagemAviso\">Você não tem mensagem!</p><p id=\"mensagemApelido\"></p><p id=\"mensagemEmail\"></p><p id=\"aviso\"></p><p id=\"erros\"></p>");
            }
        });

        $('.profile').attr('id', 'empty-article');
        $('.userprofile').attr('id', 'empty-article');
        $(document).scrollTop(0);
    };

    /**
     * Função que executa o evento do botao Sair
     * @return boolean = retorna true para direcionar para a home, apos encerrar a sessao de login
     */
    this.botaoSair = function() {
        obj = {'botaoSair': true};
        ajaxPadrao('POST', obj, '', 'json', true, 'Erro ao realizar logout', function(retorno) {
            if (retorno)
                location.href = 'http://www.netlib.com.br';
        });
    };
}


