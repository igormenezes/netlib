<?php

namespace Netlib\PrincipalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="cadastro")
 */
class Cadastro {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @ORM\Column(type="string", length=30)
     */
    public $nome;

    /**
     * @ORM\Column(type="string", length=30, unique=true)
     */
    public $apelido;

    /**
     * @ORM\Column(type="string", length=50, unique=true)
     */
    public $email;

    /**
     * @ORM\Column(type="string", length=100)
     */
    public $senha;

    /**
     * @ORM\Column(type="boolean", length=1)
     */
    public $sexo;

    /**
     * @ORM\Column(type="integer", length=1)
     */
    public $filtro;

    /**
     * @ORM\Column(type="text")
     */
    public $interesses;

    /**
     * @ORM\Column(type="string", length=100)
     */
    public $fotos;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    public $favoritos;

    /**
     * @ORM\Column(type="integer", length=1)
     */
    public $status;

    /**
     * @ORM\Column(type="string", length=30)
     */
    public $token;

    /**
     * @ORM\Column(type="integer", length=1)
     */
    public $ativo;

    public function getId() {
        return $this->id;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function setApelido($apelido) {
        $this->apelido = $apelido;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setSenha($senha) {
        $this->senha = $senha;
    }

    public function setSexo($sexo) {
        $this->sexo = $sexo;
    }

    public function setFiltro($filtro) {
        $this->filtro = $filtro;
    }

    public function setInteresses($interesses) {
        $this->interesses = $interesses;
    }

    public function setFotos($fotos) {
        $this->fotos = $fotos;
    }

    public function setFavoritos($favoritos) {
        $this->favoritos = $favoritos;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function setToken($token) {
        $this->token = $token;
    }

    public function setAtivo($ativo) {
        $this->ativo = $ativo;
    }

}
