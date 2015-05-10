function Perfil() {
    var obj; //propriedade para definir os dados do objeto, que seram enviados para o ajax
    var nome; //proprieade que recebe o nome da foto
    var nomeMaisculo; //propriedade que recebe o nome da foto com a inicial maiuscula
    var options; //propriedade que recebe os valores de configuração do ajax
    var i = 0; //propriedade para controle de loop

    /**
     * Função para realizar upload, enviar foto por ajax
     * @param {integer} id = recebe o id do elemento clicado, para poder pegar o nome da foto atraves do id
     * @return string = retorna o caminho da foto temporaria
     * @return boolean = retorna true depois de deletar o caminho da foto temporaria
     */
    this.enviarFoto = function(id) {
        nome = id.substring(7);
        obj = {'ajaxFoto': true, 'upload': nome};
        options = configAjaxForm('POST', obj, '', 'json', true, 'Erro ao enviar foto', function(retorno) {
            $('#' + nome).attr('src', retorno);
            obj = {'ajaxFoto': true, 'foto': $('#' + nome).attr('src')};
            setTimeout(function() {
                ajaxPadrao('POST', obj, $('#formulario_perfil').attr('action'), 'json', false, 'Erro ao deletar foto temporaria', true);
                switch (id) {
                    case 'perfil_foto1':
                        $('#removerFoto1').css('display', 'block');
                        break;
                    case 'perfil_foto2':
                        $('#removerFoto2').css('display', 'block');
                        break;
                    case 'perfil_foto3':
                        $('#removerFoto3').css('display', 'block');
                        break;
                    default:
                        break;
                }
            }, 500);
        });
        $('#formulario_perfil').ajaxSubmit(options);
    };

    /**
     * Função para remover a foto do perfil
     * @param {integer} id = recebe o id do elemento clicado, para poder pegar o nome da foto atraves do id
     * return boolean = retorna true caso a foto foi removida
     */
    this.removerFoto = function(id) {
        nomeMaisculo = id.substring(7);
        nome = nomeMaisculo.toLowerCase();
        var foto = $('#' + nome).attr('src').split('/');
        obj = {'pastaFoto': $('#' + nome).attr('src'), 'nomeFoto': foto[3]};

        if (foto[3])
            ajaxPadrao('POST', obj, '', 'json', true, 'Erro ao remover a foto', true);

        $(function() {
            $("<div>Foto excluida com sucesso!</div>").dialog({
                autoOpen: true,
                title: 'Mensagem',
                buttons: {
                    "OK": function() {
                        $(this).dialog("close");
                        $('#' + nome).replaceWith('<img id=\'' + nome + '\' class="img-fade" src="images/gal/tmb.jpg" alt="ProfileThumb" />'); //usando replace, em vez do attr, para evitar bug no Chrome
                        $('#remover' + nomeMaisculo).css('display', 'none');

                        $('#formulario_perfil input').each(function() {
                            if ($(this).attr('id') == 'perfil_' + nome)
                                $(this).val('');
                        });
                    }
                },
                height: "auto",
                width: "auto"
            });
        });
    };

    /**
     * Função para validacao do perfil
     * @return obj = retorna os erros de validacao
     */
    this.alterarPerfil = function() {
        obj = {
            'removerPerfil': $('#perfil').attr('src'),
            'removerFoto1': $('#foto1').attr('src'),
            'removerFoto2': $('#foto2').attr('src'),
            'removerFoto3': $('#foto3').attr('src')
        };
        options = configAjaxForm('POST', null, '', 'json', true, 'Erro na alteração do perfil', function(retorno) {
            $('#erros').html('');
            $('#alerts').attr('class', 'alerts-off');
            if (retorno.erros) {
                for (i in retorno.erros) {
                    $('#erros').append(retorno.erros[i] + '<br />');
                    $('#alerts').attr('class', 'alerts-on');
                }
                $(document).scrollTop(0);
            } else {
                $(function() {
                    $("<div>Alteração realizada com sucesso!</div>").dialog({
                        autoOpen: true,
                        title: 'Mensagem',
                        buttons: {
                            "OK": function() {
                                $('.userprofile').attr('id', 'empty-article');
                                $(this).dialog("close");
                            }
                        },
                        height: "auto",
                        width: "auto"
                    });
                });
            }
        });

        $('#formulario_perfil').ajaxForm(options);

        $('#removerFoto1').css('display', 'none'); //linha para corrigir bug de aparecer o botao, sendo que a tela foi fechada
        $('#removerFoto2').css('display', 'none'); //linha para corrigir bug de aparecer o botao, sendo que a tela foi fechada
        $('#removerFoto3').css('display', 'none'); //linha para corrigir bug de aparecer o botao, sendo que a tela foi fechada
    };

    /**
     * Função que retorna os perfis da busca ou do favoritos
     * @param {string} id = recebe o id, que é o apelido do usuário selecionado
     * @returns boolean = true retorna os perfis, false não retorna
     */
    this.mostrarPerfil = function(id) {
        obj = {'perfilBusca': id};
        ajaxPadrao('POST', obj, '', 'json', true, 'Erro para mostrar o restante da busca/favoritos', function(retorno) {
            if (retorno) {
                $('#fotoPerfil').html('');
                $('#galeriaFotos').html('');
                $('#descricaoPerfil').html('');
                $('#linksAcao').html('');

                var fotos = retorno.fotos.split('|');
                $('#apelidoBusca').html(retorno.apelido);

                for (i in fotos) {
                    if (fotos[i].substring(0, 2) == 'PE')
                        $('#fotoPerfil').html('<figure class="figure"><img class="img-fade" src=\'sistema/albuns/' + retorno.apelido + '/' + fotos[i] + '\' alt="ProfileThumb"></figure>');
                    else
                        $('#galeriaFotos').append('<section class="photo-section"><img class="img-fade" src=\'sistema/albuns/' + retorno.apelido + '/' + fotos[i] + '\' alt="ProfileThumb"></section>');
                }
                $('#descricaoPerfil').html(retorno.interesses);
                $('#linksAcao').html('<ul class="menu-list"><li><a href=\'javascript:void(0)\'  alt=' + retorno.apelido + ' onclick=mensagem.mostrarChat($(this).attr(\'alt\'))>Mensagem</a></li><li><a href=\'javascript:void(0)\' onclick=favoritos.adicionarFavoritos($(this).attr(\'alt\')) alt=' + retorno.apelido + '>Favoritar</a></li><li><a href=\"http://www.netlib.com.br/denunciar/' + retorno.apelido + '\" target=\"_blank\">Denunciar</a></li></ul>');
            }
        });

        $('#removerFoto1').css('display', 'none'); //linha para corrigir bug de aparecer o botao, sendo que a tela foi fechada
        $('#removerFoto2').css('display', 'none'); //linha para corrigir bug de aparecer o botao, sendo que a tela foi fechada
        $('#removerFoto3').css('display', 'none'); //linha para corrigir bug de aparecer o botao, sendo que a tela foi fechada
        $('#alerts').attr('class', 'alerts-off');
        $('#alerts').html("<p id=\"mensagemAviso\"><p id=\"mensagemApelido\"></p><p id=\"mensagemEmail\"></p><p id=\"aviso\"></p><p id=\"erros\"></p>");
        $('.userprofile').attr('id', 'empty-article');
        $('.chat-messages-viewport').attr('id', 'empty-article');
        $('.profile').attr('id', 'fill-article');
        $('#contatos').empty();
        $(document).scrollTop(0);
    };
}

