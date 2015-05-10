<?php

namespace Netlib\PrincipalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ErrosController extends Controller {

    public function errosAction() {
        return $this->redirect($this->generateUrl('netlib_home'));
    }

}
