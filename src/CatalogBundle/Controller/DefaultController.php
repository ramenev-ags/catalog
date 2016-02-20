<?php

namespace CatalogBundle\Controller;

use CatalogBundle\Entity\Rubric;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        return $this->render('CatalogBundle:Default:index.html.twig');
    }
}
