function Busca() {
    var obj; //propriedade para definir os dados do objeto, que seram enviados para o ajax
    var options; //propriedade que recebe os valores de configuração do ajax
    var carregar; //propriedade para carregar a busca a partir da linha x da coluna de acordo com o limite por pagina, (vezes) a quantidade de pesquisas referente a mesma busca
    var random; //propriedade boolean, true carregar usuarios randomicos, null carregar usuarios com base no interesse
    var i = 0; //propriedade para controle de loop
    var termino; //propriedade para verificar quando acabou os resultados da busca
    
    /**
     * Função para receber usuários do site aleatoriamente para exibir na home so usuário logado
     * @returns obj = retorna os usuários para exibir na home
     */
    this.BuscarUsuariosHome = function() {
        obj = {'buscarUsuariosHome': true};
        ajaxPadrao('POST', obj, '', 'json', false, 'Erro na busca de usuários para a home', function(retorno) {
            if (retorno) {
                for (i in retorno.dados)
                    $('#search-results').append('<li class="search-result"><a href=\'javascript:void(0);\'><img onclick=perfis.mostrarPerfil($(this).attr(\'title\')) class=\"search-img\" src=\'sistema/albuns/' + retorno.dados[i]['apelido'] + '/' + retorno.dados[i]['foto'] + '\' title=\'' + retorno.dados[i]['apelido'] + '\'></a></li>');
            }
        });
    }

    /**
     * Função para realizar a pesquisa de interesses do usuário, caso não encontrar usuarios, mostrar usuarios cadastrados no site para o usuario como sugestão
     * @returns obj = retorna os usuarios, com os mesmos interesses buscado
     */
    this.pesquisarInteresses = function() {
        $('#removerFoto1').css('display', 'none'); //linha para corrigir bug de aparecer o botao, sendo que a tela foi fechada
        $('#removerFoto2').css('display', 'none'); //linha para corrigir bug de aparecer o botao, sendo que a tela foi fechada
        $('#removerFoto3').css('display', 'none'); //linha para corrigir bug de aparecer o botao, sendo que a tela foi fechada
        $('#alerts').attr('class', 'alerts-off');
        $('#alerts').html("<p id=\"mensagemAviso\"></p><p id=\"mensagemApelido\"></p><p id=\"mensagemEmail\"></p><p id=\"aviso\"></p><p id=\"erros\"></p>");
        $('.profile').attr('id', 'empty-article');
        $('.userprofile').attr('id', 'empty-article');
        $('.chat-messages-viewport').attr('id', 'empty-article');
        $('#search-results').empty();
        $('#search-favorites').html('');
        $('#contatos').empty();
        carregar = null;
        random = null;
        termino = null;
        options = configAjaxForm('POST', null, '', 'json', true, 'Erro na busca', function(retorno) {
            if (retorno.dados) {
                $('#search-results').html('');
                carregar = retorno.carregar;
                for (i in retorno.dados)
                    $('#search-results').append('<li class="search-result"><a href=\'javascript:void(0);\'><img onclick=perfis.mostrarPerfil($(this).attr(\'title\')) class=\"search-img\" src=\'sistema/albuns/' + retorno.dados[i]['apelido'] + '/' + retorno.dados[i]['foto'] + '\' title=\'' + retorno.dados[i]['apelido'] + '\'></a></li>');
            } else if (retorno.erros) {
                $(function() {
                    $("<div>Preencha o campo de busca, com minimo de 4 caracteres e no máximo 800!</div>").dialog({
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
                obj = {'buscaRandom': true};
                ajaxPadrao('POST', obj, '', 'json', true, 'Erro na busca randomica', function(retorno) {
                    $('#alerts').attr('class', 'alerts-on');
                    $('#mensagemAviso').html('Não foi encontrado ninguém em sua busca!');
                    if (retorno.dados) {
                        $('#mensagemAviso').html('Não foi encontrado nenhum usuário com base em sua busca. Mas nós sugerimos outros usuários!');
                        carregar = retorno.carregar;
                        random = true;
                        for (i in retorno.dados)
                            $('#search-results').append('<li class="search-result"><a href=\'javascript:void(0);\'><img onclick=perfis.mostrarPerfil($(this).attr(\'title\')) class=\"search-img\" src=\'sistema/albuns/' + retorno.dados[i]['apelido'] + '/' + retorno.dados[i]['foto'] + '\' title=\'' + retorno.dados[i]['apelido'] + '\'></a></li>');
                    }
                });
            }
        });

        $('#formulario_busca').ajaxForm(options);
    };

    /**
     * Função para carregar informações da pesquisa, quando o scroll estiver no final da página
     * @returns obj = retorna os usuarios, com os mesmos interesses buscado
     */
    this.carregarResultados = function() {
        if ($(window).scrollTop() === ($(document).height() - $(window).height()) && carregar && !termino) {
            obj = {'scroll': true, 'carregar': (carregar), 'random': random};
            ajaxPadrao('POST', obj, '', 'json', true, 'Erro para mostrar o restante da busca', function(retorno) {
                if (retorno.dados) {
                    carregar = retorno.carregar;
                    for (i in retorno.dados)
                        $('#search-results').append('<li class="search-result"><a href=\'javascript:void(0);\'><img onclick=perfis.mostrarPerfil($(this).attr(\'title\')) class=\"search-img\" src=\'sistema/albuns/' + retorno.dados[i]['apelido'] + '/' + retorno.dados[i]['foto'] + '\' title=\'' + retorno.dados[i]['apelido'] + '\'></a></li>');
                } else {
                    termino = true;
                    return true;
                }
            });
        }
    };
}



