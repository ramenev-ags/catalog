<?php
/**
 * Created by PhpStorm.
 * User: ramenev
 * Date: 03.04.15
 * Time: 16:31
 */

namespace CatalogBundle\Security\Http\Authentication;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Util\Codes;

/**
 * AuthenticationFailureHandler
 *
 * @author Ramenev Dmitriy <diman4k@gmail.com>
 */
class AuthenticationFailureHandler extends FOSRestController implements AuthenticationFailureHandlerInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @param EventDispatcherInterface $dispatcher
     * @param ContainerInterface $container
     */
    public function __construct(EventDispatcherInterface $dispatcher, ContainerInterface $container)
    {
        $this->dispatcher = $dispatcher;
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
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

        $event = new AuthenticationFailureEvent($request, $exception, $this->handleView($view));

        $this->dispatcher->dispatch(Events::AUTHENTICATION_FAILURE, $event);

        return $event->getResponse();
    }
}