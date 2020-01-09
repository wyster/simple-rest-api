<?php

declare(strict_types=1);

namespace App\Middleware;

use Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Authentication\Adapter\AbstractAdapter;
use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\Diactoros\Response;

class IdentityHandler implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Process a server request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /**
         * @var AuthenticationService $authService
         */
        $authService = $this->container->get(AuthenticationServiceInterface::class);
        /**
         * @var AbstractAdapter $adapter
         */
        $adapter = $authService->getAdapter();
        // @todo проверять аутентификацию по токену, принимать из заголовков
        $adapter->setIdentity('');

        $authResult = $authService->authenticate();

        if (!$authResult->isValid()) {
            throw new Exception('Invalid auth');
        }

        return $handler->handle($request);
    }
}
