<?php

namespace Netlib\PrincipalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TermosController extends Controller {

    public function termosAction() {
        return $this->render('NetlibPrincipalBundle:Default:termos.html.twig');
    }

}
