<?php
/**
 * Created by PhpStorm.
 * User: eXPert
 * Date: 17.02.2016
 * Time: 11:46
 */

namespace CatalogBundle\Controller\API;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use CatalogBundle\Entity as Entity;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Class MainController
 * @package CatalogBundle\Controller\API
 * @author Dmitriy Ramenev <diman4k@gmail.com>
 */
class MainController extends FOSRestController
{
    /**
     * @ApiDoc(
     *  section="Authorization",
     *  resource=true,
     *  description="Get API Token",
     *  parameters={
     *       {"name"="_username", "dataType"="string", "required"=true, "description"="Username"},
     *       {"name"="_password", "dataType"="string", "required"=true, "description"="Password"},
     *  }
     * )
     *
     * @Route("/login", name="api_login_check")
     * @Method({"POST"})
     *
     * @return Response
     */
    public function loginAction(){}
}