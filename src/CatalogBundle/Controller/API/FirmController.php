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
 * Class FirmController
 * @package CatalogBundle\Controller\API
 * @author Dmitriy Ramenev <diman4k@gmail.com>
 */
class FirmController extends FOSRestController
{
    /**
     * @ApiDoc(
     *     section="Main methods",
     *     resource=true,
     *     description="Firms list",
     *     parameters={
     *          {"name"="page", "dataType"="integer", "required"=false, "description"="Page", "format"="\d+"},
     *          {"name"="page_size", "dataType"="integer", "required"=false, "description"="Page size", "format"="\d+"},
     *          {"name"="name", "dataType"="string", "required"=false, "description"="Firm name"},
     *          {"name"="city", "dataType"="string", "required"=false, "description"="City to search from"},
     *          {"name"="address", "dataType"="string", "required"=false, "description"="Firm address (without city)"},
     *          {"name"="rubric_name", "dataType"="string", "required"=false, "description"="Firm rubric name"},
     *          {"name"="building_id", "dataType"="integer", "required"=false, "description"="Firm building id", "format"="\d+"},
     *          {"name"="rubric_id", "dataType"="integer", "required"=false, "description"="Firm rubric id", "format"="\d+"},
     *          {"name"="point", "dataType"="string", "required"=false, "description"="Search point (lat,lng)", "format"="^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$"},
     *          {"name"="radius", "dataType"="integer", "required"=false, "description"="Search radius (point required) Default: 250 Range: 1-40000", "format"="\d+"},
     *          {"name"="bound[point1]", "dataType"="string", "required"=false, "description"="Top left bound point (lat, lng)", "format"="^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$"},
     *          {"name"="bound[point2]", "dataType"="string", "required"=false, "description"="Bottom right bound point (lat, lng)", "format"="^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$"}
     *     }
     * )
     *
     * @Route("/firms")
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

        $firmRepository = $em->getRepository('CatalogBundle:Firm');

        /**
         * @var AbstractPagination $pagination
         */
        $pagination = $this->get('knp_paginator')->paginate(
            $firmRepository->findByParams($request->query->all()),
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
     *     description="Get firm info",
     *     requirements={{"name"="id", "dataType"="integer", "description"="Firm id", "requirement"="\d+"}},
     *     output="CatalogBundle\Entity\Firm"
     * )
     *
     * @Route("/firms/{id}", requirements={"id" = "\d+"})
     * @Method({"GET"})
     *
     * @param Request $request
     * @param Entity\Firm $firm
     * @return Response
     */
    public function getAction(Entity\Firm $firm, Request $request){
        if($request->get('_format')){
            $request->setRequestFormat($request->get('_format'));
        }

        $view = $this->view(
            array(
                'building' => $firm,
                'code' => Codes::HTTP_OK,
                'message' => 'OK'
            ),
            Codes::HTTP_OK
        );

        return $this->handleView($view);
    }
}