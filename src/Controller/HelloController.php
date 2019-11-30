<?php declare(strict_types=1);

namespace App\Controller;

use Fig\Http\Message\StatusCodeInterface;
use Lcobucci\ContentNegotiation\UnformattedResponse;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;

class HelloController
{
    public function indexAction(RequestInterface $request)
    {
        $name = $request->getAttribute('name');
        return "Hello {$name}!";
    }

    public function jsonAction(RequestInterface $request): ResponseInterface
    {
        $result = [
            'name' => $request->getAttribute('name')
        ];

        return new UnformattedResponse(
            (new Response())
                ->withStatus(StatusCodeInterface::STATUS_CREATED)
                ->withHeader('Content-type', 'application/json'),
            $result
        );
    }
}
