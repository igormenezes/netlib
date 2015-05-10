<?php

namespace Netlib\PrincipalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="cache_busca")
 */
class CacheBusca {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @ORM\Column(type="integer")
     */
    public $id_usuario_pesquisa;

    /**
     * @ORM\Column(type="text")
     */
    public $interesse_busca;

    public function setIdUsuarioPesquisa($id) {
        $this->id_usuario_pesquisa = $id;
    }

    public function setInteresseBusca($interesse) {
        $this->interesse_busca = $interesse;
    }
    
    public function getId(){
        return $this->id;
    }

}