<?php

namespace Netlib\PrincipalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class FaqController extends Controller {

    public function faqAction() {
        return $this->render('NetlibPrincipalBundle:Default:faq.html.twig');
    }

}
