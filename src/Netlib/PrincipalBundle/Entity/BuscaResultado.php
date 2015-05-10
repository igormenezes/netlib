<?php

namespace Netlib\PrincipalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="busca_resultado")
 */
class BuscaResultado {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @ORM\Column(type="integer")
     * @ORM\ManyToOne(targetEntity="BuscaResultado")
     * @ORM\JoinColumn(name="cache_busca_id", referencedColumnName="id")
     */
    public $cache_busca_id;

    /**
     * @ORM\Column(type="integer")
     */
    public $id_usuario_resultado;

    /**
     * @ORM\Column(type="text")
     */
    public $interesse_resultado;

    public function setCacheBuscaId($idBusca) {
        $this->cache_busca_id = $idBusca;
    }

    public function setIdUsuarioResultado($id) {
        $this->id_usuario_resultado = $id;
    }

    public function setInteresseResultado($interesse) {
        $this->interesse_resultado = $interesse;
    }

}
