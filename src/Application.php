<?php declare(strict_types=1);

namespace App;

use Psr\Container\ContainerInterface;
use Laminas\HttpHandlerRunner\RequestHandlerRunner;
use Laminas\Stratigility\MiddlewarePipeInterface;

final class Application
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
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    public function run(): void
    {
        $this->prepareMiddleware();

        $handler = $this->getContainer()->get(RequestHandlerRunner::class);
        $handler->run();
    }

    private function prepareMiddleware(): void
    {
        $pipe = $this->getContainer()->get(MiddlewarePipeInterface::class);

        $middleware = require BASE_DIR . '/config/middleware.php';
        foreach ($middleware as $class) {
            $pipe->pipe($this->getContainer()->get($class));
        }
    }
}
