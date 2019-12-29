<?php

declare(strict_types=1);

namespace App\Middleware;

use DI\Container;
use Exception;
use Middlewares\Utils\CallableHandler;
use Middlewares\Utils\RequestHandlerContainer;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

/**
 * Адаптированная версия
 * @see \Middlewares\RequestHandler
 */
class RequestHandler implements MiddlewareInterface
{
    /**
     * @var Container|ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * @var string Attribute name for handler reference
     */
    private string $handlerAttribute = 'request-handler';

    /**
     * Set the resolver instance.
     */
    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container ?: new RequestHandlerContainer();
    }

    /**
     * Process a server request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestHandler = $request->getAttribute($this->handlerAttribute);

        if (is_array($requestHandler) && count($requestHandler) === 2 && is_string($requestHandler[0])) {
            $requestHandler[0] = $this->container->get($requestHandler[0]);
        }

        if (is_callable($requestHandler)) {
            $func = $requestHandler;
            if (is_array($requestHandler)) {
                $func = function () use ($requestHandler, $request) {
                    if (!$this->container instanceof Container) {
                        throw new Exception('Not support only PHP-DI/PHP-DI container');
                    }
                    return $this->container->call(
                        [
                            $requestHandler[0],
                            $requestHandler[1],
                        ],
                        [$request]
                    );
                };
            }

            return (new CallableHandler($func))->process($request, $handler);
        }

        throw new RuntimeException(sprintf('Invalid request handler: %s', gettype($requestHandler)));
    }
}
