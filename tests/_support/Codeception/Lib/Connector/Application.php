<?php declare(strict_types=1);

namespace Codeception\Lib\Connector;

use Helper\ResponseEmitter;
use Psr\Container\ContainerInterface;
use Symfony\Component\BrowserKit\AbstractBrowser as Client;
use Symfony\Component\BrowserKit\Response;
use Codeception\Lib\Connector\Shared\PhpSuperGlobalsConverter;
use Throwable;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\Stream;
use Laminas\HttpHandlerRunner\Emitter;
use Laminas\HttpHandlerRunner\RequestHandlerRunner;
use Laminas\Stratigility\MiddlewarePipeInterface;

class Application extends Client
{
    use PhpSuperGlobalsConverter;

    /**
     * @var \App\Application
     */
    private $application;

    /**
     * @param \App\Application $application
     */
    public function setApplication(\App\Application $application)
    {
        $this->application = $application;
    }

    /**
     * @return \App\Application
     */
    public function getApplication(): \App\Application
    {
        return $this->application;
    }

    /**
     * Makes a request.
     *
     * @param \Symfony\Component\BrowserKit\Request $request
     *
     * @return \Symfony\Component\BrowserKit\Response
     * @throws \RuntimeException
     */
    public function doRequest($request)
    {
        $container = $this->getApplication()->getContainer();
        $container->set(
            Emitter\EmitterInterface::class,
            function () {
                return new ResponseEmitter();
            }
        );

        $uri = $request->getUri();
        $pathString = parse_url($uri, PHP_URL_PATH);
        $queryString = parse_url($uri, PHP_URL_QUERY) ?: '';
        $_SERVER = $request->getServer();
        $_SERVER['REQUEST_METHOD'] = strtoupper($request->getMethod());
        $_SERVER['REQUEST_URI'] = null === $queryString ? $pathString : $pathString . '?' . $queryString;
        $_COOKIE = $request->getCookies();
        $_FILES = $this->remapFiles($request->getFiles());
        $_REQUEST = $this->remapRequestParameters($request->getParameters());
        $_POST = [];
        $_GET = [];
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $_GET = $_REQUEST;
        } else {
            $_POST = $_REQUEST;
        }
        parse_str($queryString, $output);
        foreach ($output as $k => $v) {
            $_GET[$k] = $v;
        }
        $_GET['_url'] = $pathString;
        $_SERVER['QUERY_STRING'] = http_build_query($_GET);

        $container->set(RequestHandlerRunner::class, static function (ContainerInterface $c) {
            return new RequestHandlerRunner(
                $c->get(MiddlewarePipeInterface::class),
                $c->get(Emitter\EmitterInterface::class),
                $c->get(ServerRequestFactory::class),
                static function (Throwable $e) {
                    throw $e;
                }
            );
        });
        $container->set(ServerRequestFactory::class, static function () use ($request) {
            return static function () use ($request) {
                $stream = new Stream('php://temp', 'wr');
                $stream->write($request->getContent() ?: '');
                $serverRequest = ServerRequestFactory::fromGlobals();
                return $serverRequest->withBody($stream);
            };
        });

        $this->getApplication()->run();

        $response = $container->get(Emitter\EmitterInterface::class)->getResponse();
        $status = $response->getStatusCode();

        return new Response(
            (string)$response->getBody() ?: '',
            $status ? $status : 200,
            $response->getHeaders()
        );
    }
}
