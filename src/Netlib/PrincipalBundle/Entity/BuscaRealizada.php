<?php

namespace Netlib\PrincipalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="busca_realizada")
 */
class BuscaRealizada {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @ORM\Column(type="integer")
     * @ORM\ManyToOne(targetEntity="BuscaRealizada")
     * @ORM\JoinColumn(name="cache_busca_id", referencedColumnName="id")
     */
    public $cache_busca_id;

    /**
     * @ORM\Column(type="string", length=30)
     */
    public $tag_pesquisa;
    
    public function setCacheBuscaId($idBusca) {
        $this->cache_busca_id = $idBusca;
    }

    public function setTagPesquisa($tag) {
        $this->tag_pesquisa = $tag;
    }

}