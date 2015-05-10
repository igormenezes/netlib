<?php

namespace Netlib\PrincipalBundle\Manager;

class ImagemManager {

    private $pasta = 'sistema/albuns/'; //propriedade que armazena o caminho da pasta das fotos do album
    private $pastaTemporaria = 'sistema/temp/'; //propriedade que armazena o caminho da pasta das fotos temporarias do album

    /*
      LINUX
      $arrayNome = explode('/', $file);
      $foto = 'temp/' . date('d-m-Y') . $arrayNome[2] . '.jpg';

      WINDOWS
      $arrayNome = explode('\\', $file);
      $foto = 'temp/' . date('d-m-Y') . $arrayNome[3] . '.jpg'; */

    /**
     * Função para salvar as fotos na pasta do usuario
     * @param string $apelido = apelido do usuario que irá salvar ou editar as imagens
     * @param string $formulario = verifica se é cadastro de novas imagens(cadastro) ou edição de imagens(perfil)
     * @return string retorna uma string com o nome das fotos separados por | ex: perfil.jpg|foto1.jpg|foto2.jpg para depois inserir no banco de dados
     */
    public function salvarImagens($apelido, $formulario) {
        try {
            $pasta = "$this->pasta{$apelido}/";

            if ($formulario == 'perfil')
                $fotosAntigas = $this->trocarImagens($pasta);
            else
                mkdir($pasta);

            foreach ($_FILES[$formulario]['name'] as $chave => $valor) {
                $array = explode('/', $_FILES[$formulario]['tmp_name'][$chave]);
                switch ($chave) {
                    case 'perfil':
                        $foto = $pasta . 'PE' . $array[2] . '.jpg';
                        $nomeFoto = 'PE' . $array[2] . '.jpg';
                        move_uploaded_file($_FILES[$formulario]['tmp_name']['perfil'], $foto);
                        $this->editarTamanhoImagem($foto);
                        $arrayFotos[] = $nomeFoto;
                        break;
                    case 'foto1':
                        $foto = $pasta . 'F1' . $array[2] . '.jpg';
                        $nomeFoto = 'F1' . $array[2] . '.jpg';
                        move_uploaded_file($_FILES[$formulario]['tmp_name']['foto1'], $foto);
                        $this->editarTamanhoImagem($foto);
                        $arrayFotos[] = $nomeFoto;
                        break;
                    case 'foto2':
                        $foto = $pasta . 'F2' . $array[2] . '.jpg';
                        $nomeFoto = 'F2' . $array[2] . '.jpg';
                        move_uploaded_file($_FILES[$formulario]['tmp_name']['foto2'], $foto);
                        $this->editarTamanhoImagem($foto);
                        $arrayFotos[] = $nomeFoto;
                        break;
                    case 'foto3':
                        $foto = $pasta . 'F3' . $array[2] . '.jpg';
                        $nomeFoto = 'F3' . $array[2] . '.jpg';
                        move_uploaded_file($_FILES[$formulario]['tmp_name']['foto3'], $foto);
                        $this->editarTamanhoImagem($foto);
                        $arrayFotos[] = $nomeFoto;
                        break;
                }
            }

            switch ($formulario) {
                case 'perfil':
                    return $this->filtrarImagens($fotosAntigas, $arrayFotos);
                    break;
                case 'cadastro':
                    return $fotos = implode('|', $arrayFotos);
                    break;
            }
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Ocorreu um erro no armazenamento das fotos do usuário' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Ocorreu um erro no armazenamento das fotos do usuário');
        }
    }

    /**
     * Função para salvar temporariamente as imagens que foram chamadas pelo ajax que ainda não foram enviadas pelo formulário
     * @param array $file = array contendo os $_FILES['key']['valor'] key e valor determinados na hora de passar para a função
     * @return string = retorna o caminho da pasta onde a foto temporaria está armazenada
     */
    public function salvarImagensTemporarias($file) {
        try {
            $arrayNome = explode('/', $file);
            $foto = $this->pastaTemporaria . date('d-m-Y') . $arrayNome[2] . '.jpg';
            move_uploaded_file($file, $foto);
            $this->editarTamanhoImagem($foto);
            return $foto;
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Ocorreu um erro para salvar as fotos temporarias' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Ocorreu um erro para salvar as fotos temporarias');
        }
    }

    /**
     * Função para trocar as fotos antigas pelas novas fotos! 
     * Essa função é utilizada para evitar repetição de foto de perfil ou do album, pois os nomes das fotos são gerados automaticamente.
     * Mesmo que não utilizadas, ficariam muitas fotos na pasta, em um futuro proximo poderia ter problema com processamento, por conta em espaço em disco.
     * @param type $pasta
     * @return array = retorna um array com as fotos antigas que não serão deletadas
     */
    private function trocarImagens($pasta) {
        try {
            $dir = opendir($pasta);
            while ($file = readdir($dir)) {
                if ($file != '.' && $file != '..')
                    $arrayFile[] = $file;
            }

            foreach ($arrayFile as $chaveFile => $file) {
                foreach ($_FILES['perfil']['name'] as $chave => $valor) {
                    switch ($chave) {
                        case 'perfil':
                            if (substr($file, 0, 2) == 'PE') {
                                unlink($pasta . $file);
                                unset($arrayFile[$chaveFile]);
                            }
                            break;
                        case 'foto1':
                            if (substr($file, 0, 2) == 'F1') {
                                unlink($pasta . $file);
                                unset($arrayFile[$chaveFile]);
                            }
                            break;
                        case 'foto2':
                            if (substr($file, 0, 2) == 'F2') {
                                unlink($pasta . $file);
                                unset($arrayFile[$chaveFile]);
                            }
                            break;
                        case 'foto3':
                            if (substr($file, 0, 2) == 'F3') {
                                unlink($pasta . $file);
                                unset($arrayFile[$chaveFile]);
                            }
                            break;
                    }
                }
            }
            closedir($dir);
            return $arrayFile;
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Ocorreu um erro na remoção das fotos' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Ocorreu um erro na remoção das fotos');
        }
    }

    /**
     * Função para editar tamanho das imagens
     * @param string $foto = variavel contendo a foto com o caminho completo. ex: sistema/albuns/valor/imagem.jpg
     */
    private function editarTamanhoImagem($foto) {
        try {
            $tipo = getimagesize($foto);

            if ($tipo['mime'] == 'image/jpeg') {
                $tamanho = 300;
                $imagemOriginal = imagecreatefromjpeg($foto);
                $alturaOriginal = imagesy($imagemOriginal);
                $larguraOriginal = imagesx($imagemOriginal);
                $imagemFinal = imagecreatetruecolor($tamanho, $tamanho);
                imagecopyresampled($imagemFinal, $imagemOriginal, 0, 0, 0, 0, $tamanho, $tamanho, $larguraOriginal, $alturaOriginal);
                imagejpeg($imagemFinal, $foto);
            }
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao editar tamanho da foto' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao editar tamanho da foto');
        }
    }

    /**
     * Função para realizar filtro das fotos novas e antigas, para pode pegar as fotos antigas, que não foram substituidas
     * Assim temos todas as fotos que estão sendo utilizadas no perfil do usuario no nosso banco de dados..
     * @param array $fotosAntigas = array contendo as fotos antigas, que não foram substituidas por novas fotos
     * @param array $fotosNovas = array contendo as fotos novas, que foram alteradas pelo usuário
     * @return string = retorna uma string contendo o nome de todas as fotos que serão usadas no perfil, separadas por | ex: perfil.jpg|foto1.jpg ...
     */
    private function filtrarImagens($fotosAntigas, $fotosNovas) {
        if ($fotosAntigas) {
            $stringFotosNovas = implode('|', $fotosNovas);
            $stringFotosAntigas = implode('|', $fotosAntigas);
            return $fotos = $stringFotosNovas . '|' . $stringFotosAntigas;
        } else {
            return $fotos = implode('|', $fotosNovas);
        }
    }

}
