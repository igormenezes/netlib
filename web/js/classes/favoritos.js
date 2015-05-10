function Favoritos() {
    var obj; //propriedade para definir os dados do objeto, que seram enviados para o ajax
    var carregar; //quantidade de vezes que foi chamado o evento para carregar os usuários dos favoritos (é acrescetado quando descer scroll até o o final e receber a continuação existente de usuario dos favoritos)
    var i = 0; //propriedade para controle de loop
    var j = 0; //propriedade para controle de loop
    var limite; //propriedade que define limite de X pessoas dos favoritos por carregamento
    var usuarios; //propriedade que contem o objeto dos usuarios presentes no favoritos
    var pos; //posicao de carregamento de onde que parou, para quando descer o scroll, voltar a carregar os favoritos de onde parou
    var termino; //propriedade para verificar quando acabou de carregar todo o favoritos

    /**
     * Função para receber favoritos
     * @returns {obj} = retorna os usuários que estão nos favoritos
     */
    this.receberFavoritos = function() {
        $('#alerts').attr('class', 'alerts-off');
        $('#alerts').html("<p id=\"mensagemAviso\"></p><p id=\"mensagemApelido\"></p><p id=\"mensagemEmail\"></p><p id=\"aviso\"></p><p id=\"erros\"></p>");
        
        obj = {'botaoFavoritos': true};
        ajaxPadrao('POST', obj, '', 'json', true, 'Erro ao receber lista de favoritos', function(retorno) {
            if (retorno) {
                $('#search-favorites').html('');
                $(window).scrollTop(0);
                carregar = 1;
                limite = 30;
                pos = null;
                termino = null;
                usuarios = retorno;

                for (i in retorno) {
                    if (i == limite) {
                        carregar++;
                        pos = i;
                        break;
                    }

                    var fotos = retorno[i].fotos.split('|');

                    for (j in fotos) {
                        if (fotos[j].substring(0, 2) == 'PE')
                           $('#search-favorites').append('<li alt=\'' + retorno[i].apelido + '\' class="search-result"><figure class="search-bookmark"><a href=\'javascript:void(0)\'><img onclick=perfis.mostrarPerfil($(this).attr(\'title\')) title=\'' + retorno[i].apelido + '\' src=\'sistema/albuns/' + retorno[i].apelido + '/' + fotos[j] + '\' class="search-img" src="images/gal/tmb.jpg" alt="Foto de um Favorito"></a></figure><footer><ul class="menu-image"><li><a class="left" href=\'javascript:void(0)\' alt=\'' + retorno[i].apelido + '\' onclick=mensagem.mostrarChat($(this).attr(\'alt\')) aria-label="Mensagem" data-icon="&#61446;" value="Menu"></a></li><li><a class="right" href=\'javascript:void(0)\' onclick=favoritos.excluirFavoritos($(this).attr(\'alt\')) alt=\'' + retorno[i].apelido + '\' aria-label="Remover" data-icon="&#61774;" value="Menu"></a></li></ul></footer></li');
                    }
                }
            } else {
                $('#alerts').attr('class', 'alerts-on');
                $('#mensagemAviso').html('Você não tem nenhum usuário nos seus favoritos');
            }
        });
        $('#removerFoto1').css('display', 'none'); //linha para corrigir bug de aparecer o botao, sendo que a tela foi fechada
        $('#removerFoto2').css('display', 'none'); //linha para corrigir bug de aparecer o botao, sendo que a tela foi fechada
        $('#removerFoto3').css('display', 'none'); //linha para corrigir bug de aparecer o botao, sendo que a tela foi fechada
        $('.userprofile').attr('id', 'empty-article');
        $('.profile').attr('id', 'empty-article');
        $('.chat-messages-viewport').attr('id', 'empty-article');
        $('#contatos').empty();
        $('#search-results').empty();
        $('#search-favorites').empty();
    };
    
    /**
     * Função para carregar o restante dos favoritos, conforme for descendo o scroll
     */
    this.carregarFavoritos = function() {
        if ($(window).scrollTop() === ($(document).height() - $(window).height()) && carregar && !termino) {
            limite = limite * carregar;
            var quantidadeUsuarios = usuarios.length;

            for (var i = pos; i <= quantidadeUsuarios; i++) {
                if (i == quantidadeUsuarios) {
                    termino = true;
                    break;
                }
                if (pos == limite) {
                    carregar++;
                    pos = i;
                    break;
                }

                var fotos = usuarios[pos].fotos.split('|');

                for (j in fotos) {
                    if (fotos[j].substring(0, 2) == 'PE')
                        $('#search-favorites').append('<li alt=\'' + usuarios[i].apelido + '\' class="search-result"><figure class="search-bookmark"><a href=\'javascript:void(0)\'><img onclick=perfis.mostrarPerfil($(this).attr(\'title\')) title=\'' + usuarios[i].apelido + '\' src=\'sistema/albuns/' + usuarios[i].apelido + '/' + fotos[j] + '\' class="search-img" src="images/gal/tmb.jpg" alt="Foto de um Favorito"></a></figure><footer><ul class="menu-image"><li><a class="left" href=\'javascript:void(0)\' alt=\'' + usuarios[i].apelido + '\' onclick=mensagem.mostrarChat($(this).attr(\'alt\')) aria-label="Mensagem" data-icon="&#61446;" value="Menu"></a></li><li><a class="right" href=\'javascript:void(0)\' onclick=favoritos.excluirFavoritos($(this).attr(\'alt\')) alt=\'' + usuarios[i].apelido + '\' aria-label="Remover" data-icon="&#61774;" value="Menu"></a></li></ul></footer></li');
                }
                pos++;
            }
        }
    };

    /**
     * Função para adicionar usuário selecionado aos favoritos
     * @param {string} id = apelido do usuário
     * @return boolean = true caso o usuário já foi adicionado. False, caso o usuário ainda não foi adicionado, então adiciona
     */
    this.adicionarFavoritos = function(id) {
        $(function() {
            $("<div>Deseja adicionar essa pessoa nos favoritos?</div>").dialog({
                autoOpen: true,
                title: 'Mensagem',
                buttons: {
                    "Sim": function() {
                        $(this).dialog("close");
                        obj = {'verificarFavoritos': id};
                        ajaxPadrao('POST', obj, '', 'json', true, 'Erro ao adicionar usuário nos favoritos', function(retorno) {
                            if (retorno) {
                                $(function() {
                                    $("<div>Esse usuário já foi adicionado nos favoritos!</div>").dialog({
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
                                obj = {'adicionarFavoritos': id};
                                ajaxPadrao('POST', obj, '', 'json', true, 'Erro ao adicionar usuário nos favoritos', function(retorno) {
                                    if (retorno) {
                                        $(function() {
                                            $("<div>Usuário adicionado aos favoritos!</div>").dialog({
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
                                    }
                                });
                            }
                        });
                    },
                    "Não": function() {
                        $(this).dialog("close");
                    }
                }
            });
        });
    };

    /**
     * Função para excluir usuário dos favoritos
     * @param {integer} id
     * @returns {boolean} = true, o usuário foi deletado
     */
    this.excluirFavoritos = function(id) {
        obj = {'excluirFavoritos': id};
        ajaxPadrao('POST', obj, '', 'json', true, 'Erro ao excluir usuário nos favoritos', function(retorno) {
            if (retorno) {
                $(function() {
                    $("<div>Usuário excluido dos favoritos!</div>").dialog({
                        autoOpen: true,
                        title: 'Mensagem',
                        buttons: {
                            "OK": function() {
                                $('#search-favorites li').each(function() {
                                    if ($(this).attr('alt') == id)
                                        $(this).remove();
                                });

                                if ($('#search-favorites li').size() == 0){
                                     $('#alerts').attr('class', 'alerts-on');
                                     $('#mensagemAviso').html('Você não tem nenhum usuário nos favoritos');
                                }
                                
                                $(this).dialog("close");
                            }
                        },
                        height: "auto",
                        width: "auto"
                    });
                });
            }
            ;
        });
    };
}

