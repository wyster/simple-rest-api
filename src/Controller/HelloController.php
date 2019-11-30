<?php declare(strict_types=1);

namespace App\Controller;

use Fig\Http\Message\StatusCodeInterface;
use Lcobucci\ContentNegotiation\UnformattedResponse;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Diactoros\Response;

class HelloController
{
    /**
     * @param RequestInterface $request
     * @param Adapter $db
     * @return string
     */
    public function indexAction(RequestInterface $request, AdapterInterface $db)
    {
        $now = $db->query('select now()')->execute()->current()['now'];
        $name = $request->getAttribute('name');
        return "Hello {$name}, now: {$now}!";
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
