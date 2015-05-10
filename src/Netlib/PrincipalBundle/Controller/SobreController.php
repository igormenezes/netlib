<?php

namespace Netlib\PrincipalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SobreController extends Controller {

    public function sobreAction() {
        return $this->render('NetlibPrincipalBundle:Default:sobre.html.twig');
    }

}
