function Cadastro() {
    var obj; //propriedade para definir os dados do objeto, que seram enviados para o ajax
    var nome; //propriedade que recebe o nome do que vai ser verificado
    var nomeMaisculo; //propriedade que recebe o nome da foto com a inicial maiuscula
    var nomeRemover; //propriedade que recebe o nome da foto, a qual irá ser removida das fotos temporarias, nao pega o primeiro caractere por ser Maiusculo
    var options; //propriedade que recebe os valores de configuração do ajax

    /**
     * Função para verificar se o e-mail ou o apelido já existem
     * @param integer id = recebe o id do elemento clicado, para saber se irá verificar um e-mail ou apelido
     * @return boolean = retorna true caso o apelido ou e-mail já existam
     */
    this.verificarDados = function(id) {
        switch (id) {
            case 'cadastro_apelido':
                nome = 'Apelido';
                if ($('#cadastro_apelido').val() == '')
                    $('#mensagem' + nome).html('');
                if ($('#cadastro_apelido').val() == '' && $('#cadastro_email').val() == '')
                    $('#alerts').attr('class', 'alerts-off');
                obj = {'verificaApelido': $('#cadastro_apelido').val()};
                break;
            case 'cadastro_email':
                nome = 'Email';
                if ($('#cadastro_email').val() == '')
                    $('#mensagem' + nome).html('');
                obj = {verificaEmail: $('#cadastro_email').val()};
                break;
        }

        if ($('#' + id).val()) {
            ajaxPadrao('POST', obj, '', 'json', true, 'Erro na validação do apelido', function(retorno) {
                if (retorno && obj.verificaApelido) {
                    $('#alerts').attr('class', 'alerts-on');
                    $('#mensagem' + nome).html('Esse apelido já existe');
                } else if (retorno && obj.verificaEmail) {
                    $('#alerts').attr('class', 'alerts-on');
                    $('#mensagem' + nome).html('Esse email já existe');
                }

                if ($('#mensagemApelido').html() && $('#mensagemApelido').html() && !retorno) {
                    $('#alerts').attr('class', 'alerts-on');
                    $('#mensagem' + nome).html('');
                } else if ($('#mensagemApelido').html() == '' && $('#mensagemApelido').html('') == '' && !retorno) {
                    $('#alerts').attr('class', 'alerts-off');
                }
            });
        }
    };

    /**
     * Função para realizar upload, enviar foto por ajax
     * @param integer id = recebe o id do elemento clicado, para poder pegar o nome da foto atraves do id
     * @return string = retorna o caminho da foto temporaria
     * @return boolean = retorna true depois de deletar o caminho da foto temporaria
     */
    this.enviarFoto = function(id) {
        nome = id.substring(9);
        nomeRemover = nome.substring(1);
        obj = {'ajaxFoto': true, 'upload': nome};
        options = configAjaxForm('POST', obj, '', 'json', true, 'Erro ao enviar foto', function(retorno) {
            $('#' + nome).attr('src', retorno);

            if (nome != 'perfil')
                $('#removerF' + nomeRemover).css('display', 'block');

            obj = {'ajaxFoto': 'upload', 'foto': $('#' + nome).attr('src')};
            
            setTimeout(function() {
                ajaxPadrao('POST', obj, $('#formulario_cadastro').attr('action'), 'json', false, 'Erro ao deletar foto temporaria', true);
            }, 500)
        });
        $('#formulario_cadastro').ajaxSubmit(options);
    };

    /**
     * Função para remover foto
     * @param integer id = recebe o id do elemento clicado, para poder pegar o nome da foto atraves do id
     */
    this.removerFoto = function(id) {
        nomeMaisculo = id.substring(7);
        nome = nomeMaisculo.toLowerCase();
        $('#' + nome).replaceWith('<img id=\'' + nome + '\'  class="img-fade" src="images/gal/tmb.jpg" alt="ProfileThumb" />'); //usando replace, em vez do attr, para evitar bug no Chrome
        $('#remover' + nomeMaisculo).css('display', 'none');
        $('#formulario_cadastro input').each(function() {
            if ($(this).attr('id') == 'cadastro_' + nome)
                $(this).val('');
        });
    };

    /**
     * Função para validacação do formulário de cadastro
     * @return obj = retorna erros e mensagens de aviso
     * @return string = retorna OK, para poder redirecionar para a url(só funciona assim por causa do twig)
     */
    this.cadastrar = function() {
        options = configAjaxForm('POST', null, '', 'json', true, 'Erro no cadastro', function(retorno) {
            var controle = false;
            $('#alerts').attr('class', 'alerts-off');
            $('#erros').html('');
            $('#aviso').html('');
            $('#mensagemApelido').html('');
            $('#mensagemEmail').html('');
            if (retorno.erros && retorno.mensagem) {
                for (var chave in retorno.erros) {
                    $('#erros').append(retorno.erros[chave] + '<br />');
                }
                $('#aviso').html('O apelido ou login/e-mail já existe! <br />');
                $('#alerts').attr('class', 'alerts-on');
            } else if (retorno.erros) {
                for (var chave in retorno.erros) {
                    $('#erros').append(retorno.erros[chave] + '<br />');
                    $('#alerts').attr('class', 'alerts-on');
                }
            } else if (retorno.mensagem) {
                $('#aviso').html('O apelido ou login/e-mail já existe! <br />');
                $('#alerts').attr('class', 'alerts-on');
            } else {
                controle = true;
                $(function() {
                    $("<div>Você recebeu um código de ativação no seu e-mail, verifique para proseguir!</div>").dialog({
                        autoOpen: true,
                        title: 'Mensagem',
                        buttons: {
                            "OK": function() {
                                location.href = 'http://www.netlib.com.br';
                            }
                        },
                        height: "auto",
                        width: "auto"
                    });
                });
            }

            if (!controle)
                $(document).scrollTop(0);
        });

        $('#formulario_cadastro').ajaxForm(options);
    };
}

