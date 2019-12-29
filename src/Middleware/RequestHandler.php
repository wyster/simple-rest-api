<?php

declare(strict_types=1);

namespace App\Middleware;

use DI\Container;
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
     * @var bool
     */
    private bool $continueOnEmpty = false;

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
     * Set the attribute name to store handler reference.
     */
    public function handlerAttribute(string $handlerAttribute): self
    {
        $this->handlerAttribute = $handlerAttribute;

        return $this;
    }

    /**
     * Configure whether continue with the next handler if custom requestHandler is empty.
     */
    public function continueOnEmpty(bool $continueOnEmpty = true): self
    {
        $this->continueOnEmpty = $continueOnEmpty;

        return $this;
    }

    /**
     * Process a server request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestHandler = $request->getAttribute($this->handlerAttribute);

        if (empty($requestHandler)) {
            if ($this->continueOnEmpty) {
                return $handler->handle($request);
            }

            throw new RuntimeException('Empty request handler');
        }

        if (is_string($requestHandler)) {
            $requestHandler = $this->container->get($requestHandler);
        }

        if (is_array($requestHandler) && count($requestHandler) === 2 && is_string($requestHandler[0])) {
            $requestHandler[0] = $this->container->get($requestHandler[0]);
        }

        if ($requestHandler instanceof MiddlewareInterface) {
            return $requestHandler->process($request, $handler);
        }

        if ($requestHandler instanceof RequestHandlerInterface) {
            return $requestHandler->handle($request);
        }

        if (is_callable($requestHandler)) {
            if (is_array($requestHandler)) {
                $func = function () use ($requestHandler, $request) {
                    return $this->container->call(
                        [
                            $requestHandler[0],
                            $requestHandler[1],
                        ],
                        [$request]
                    );
                };
            } else {
                $func = $requestHandler;
            }

            return (new CallableHandler($func))->process($request, $handler);
        }

        throw new RuntimeException(sprintf('Invalid request handler: %s', gettype($requestHandler)));
    }
}
