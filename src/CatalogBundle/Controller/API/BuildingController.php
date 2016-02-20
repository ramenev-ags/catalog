<?php
/**
 * Created by PhpStorm.
 * User: eXPert
 * Date: 17.02.2016
 * Time: 11:46
 */

namespace CatalogBundle\Controller\API;

use Knp\Component\Pager\Pagination\AbstractPagination;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\Controller\Annotations as Rest;

use CatalogBundle\Entity as Entity;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Class BuildingController
 * @package CatalogBundle\Controller\API
 * @author Dmitriy Ramenev <diman4k@gmail.com>
 */
class BuildingController extends FOSRestController
{
    /**
     * @ApiDoc(
     *     section="Main methods",
     *     resource=true,
     *     description="Buildings list",
     *     parameters={
     *          {"name"="page", "dataType"="integer", "required"=false, "description"="Page", "format"="\d+"},
     *          {"name"="page_size", "dataType"="integer", "required"=false, "description"="Page size", "format"="\d+"},
     *          {"name"="city", "dataType"="string", "required"=false, "description"="City to search from"}
     *     }
     * )
     *
     * @Route("/buildings")
     * @Method({"GET"})
     *
     * @param Request $request
     * @return Response
     */
    public function listAction(Request $request){
        if($request->get('_format')){
            $request->setRequestFormat($request->get('_format'));
        }

        $em = $this->getDoctrine()->getManager();

        $buildingRepository = $em->getRepository('CatalogBundle:Building');

        /**
         * @var AbstractPagination $pagination
         */
        $pagination = $this->get('knp_paginator')->paginate(
            $buildingRepository->findByParams($request->query->all()),
            $request->get('page', 1)/*page number*/,
            $request->get('page_size', 25)/*limit per page*/
        );

        $view = $this->view(
            array(
                'results' => $pagination->getItems(),
                'total' => $pagination->getTotalItemCount(),
                'code' => Codes::HTTP_OK,
                'message' => 'OK'
            ),
            Codes::HTTP_OK
        );

        return $this->handleView($view);
    }

    /**
     * @ApiDoc(
     *     section="Main methods",
     *     description="Get building info",
     *     requirements={{"name"="id", "dataType"="integer", "description"="Building id", "requirement"="\d+"}},
     *     output="CatalogBundle\Entity\Building"
     * )
     *
     * @Route("/buildings/{id}", requirements={"id" = "\d+"})
     * @Method({"GET"})
     *
     * @param Request $request
     * @param Entity\Building $building
     * @return Response
     */
    public function getAction(Entity\Building $building, Request $request){
        if($request->get('_format')){
            $request->setRequestFormat($request->get('_format'));
        }

        $view = $this->view(
            array(
                'result' => $building,
                'code' => Codes::HTTP_OK,
                'message' => 'OK'
            ),
            Codes::HTTP_OK
        );

        return $this->handleView($view);
    }
}