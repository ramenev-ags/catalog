<?php
/**
 * Created by PhpStorm.
 * User: ramenev
 * Date: 06.04.15
 * Time: 12:04
 */

namespace CatalogBundle\Security\Http\EntryPoint;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Util\Codes;

/**
 * Class AuthenticationEntryPoint
 * @package CatalogBundle\Security\Http\EntryPoint
 *
 * @author Ramenev Dmitriy <diman4k@gmail.com>
 */
class AuthenticationEntryPoint extends FOSRestController implements AuthenticationEntryPointInterface {
    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        if($request->get('_format')){
            $request->setRequestFormat($request->get('_format'));
        }

        $view = $this->view(
            [
                'code'    => Codes::HTTP_UNAUTHORIZED,
                'message' => 'Invalid credentials.',
            ],
            Codes::HTTP_UNAUTHORIZED
        );

        return $this->handleView($view);
    }
}