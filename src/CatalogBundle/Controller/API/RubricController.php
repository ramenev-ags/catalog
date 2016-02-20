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
class RubricController extends FOSRestController
{
    /**
     * @ApiDoc(
     *     section="Main methods",
     *     resource=true,
     *     description="Rubrics list",
     *     parameters={
     *          {"name"="page", "dataType"="integer", "required"=false, "description"="Page", "format"="\d+"},
     *          {"name"="page_size", "dataType"="integer", "required"=false, "description"="Page size", "format"="\d+"},
     *          {"name"="node_id", "dataType"="integer", "required"=false, "description"="If you pass a node, the method will return its children", "format"="\d+"},
     *          {"name"="direct", "dataType"="boolean", "required"=false, "description"="Get direct children of the node (only the root nodes if node_id is not set)", "format"="\d+"}
     *     }
     * )
     *
     * @Route("/rubrics")
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

        $rubricRepository = $em->getRepository('CatalogBundle:Rubric');

        /**
         * @var AbstractPagination $pagination
         */
        $pagination = $this->get('knp_paginator')->paginate(
            $rubricRepository->childrenQueryBuilder(
                $request->get('node_id') ? $rubricRepository->find($request->get('node_id')) : null,
                boolval($request->get('direct', false))
            )->getQuery(),
            $request->get('page', 1)/*page number*/,
            $request->get('page_size', 25)/*limit per page*/
        );

        $results = [];

        if($request->get('node_id')){
            foreach($pagination->getItems() as $item){
                $results[] = $item->getDescendant();
            }
        }
        else{
            $results = $pagination->getItems();
        }


        $view = $this->view(
            array(
                'results' => $results,
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
     *     description="Get rubric info",
     *     requirements={{"name"="id", "dataType"="integer", "description"="Rubric id", "requirement"="\d+"}},
     *     output="CatalogBundle\Entity\Rubric"
     * )
     *
     * @Route("/rubrics/{id}", requirements={"id" = "\d+"})
     * @Method({"GET"})
     *
     * @param Request $request
     * @param Entity\Rubric $rubric
     * @return Response
     */
    public function getAction(Entity\Rubric $rubric, Request $request){
        if($request->get('_format')){
            $request->setRequestFormat($request->get('_format'));
        }

        $view = $this->view(
            array(
                'result' => $rubric,
                'code' => Codes::HTTP_OK,
                'message' => 'OK'
            ),
            Codes::HTTP_OK
        );

        return $this->handleView($view);
    }
}