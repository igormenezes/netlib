<?php

namespace Netlib\PrincipalBundle\Manager;

class TokenManager {

    /**
     * Gerar token para criar nova senha para o usuário ou para ativar um cadastro novo
     * @param string $apelido
     * @return string = retornar o token
     */
    public function gerarToken($apelido) {
        $codigo = '';

        for ($i = 0; $i <= 8; $i++) {
            switch (rand(0, 21)) {
                case 0:
                    $codigo .= 'a';
                    break;
                case 1:
                    $codigo .= 'A';
                    break;
                case 2:
                    $codigo .= 'b';
                    break;
                case 3:
                    $codigo .= 'B';
                    break;
                case 4:
                    $codigo .= 'c';
                    break;
                case 5:
                    $codigo .= 'C';
                    break;
                case 6:
                    $codigo .= 'd';
                    break;
                case 7:
                    $codigo .= 'D';
                    break;
                case 8:
                    $codigo .= 'e';
                    break;
                case 9:
                    $codigo .= 'E';
                    break;
                case 10:
                    $codigo .= 'f';
                    break;
                case 11:
                    $codigo .= 'F';
                    break;
                case 12:
                    $codigo .= 0;
                    break;
                case 13:
                    $codigo .= 1;
                    break;
                case 14:
                    $codigo .= 2;
                    break;
                case 15:
                    $codigo .= 3;
                    break;
                case 16:
                    $codigo .= 4;
                    break;
                case 17:
                    $codigo .= 5;
                    break;
                case 18:
                    $codigo .= 6;
                    break;
                case 19:
                    $codigo .= 7;
                    break;
                case 20:
                    $codigo .= 8;
                    break;
                case 21:
                    $codigo .= 9;
                    break;
            }
        }
        return $formatApelido . $codigo;
    }

}
