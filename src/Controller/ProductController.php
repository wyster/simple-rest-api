<?php declare(strict_types=1);

namespace App\Controller;

use Fig\Http\Message\StatusCodeInterface;
use Lcobucci\ContentNegotiation\UnformattedResponse;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;
use App\Model;

class ProductController
{
    public function fetchAllAction(RequestInterface $request, Model\Product $model): ResponseInterface
    {
        $result = $model->getAll()->toArray();

        $response = (new Response())
            ->withStatus(StatusCodeInterface::STATUS_OK)
            ->withHeader('Content-type', 'application/json');
        return new UnformattedResponse(
            $response,
            $result
        );
    }
}
