<?php declare(strict_types=1);

namespace App\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response;
use App\Model;

final class ProductController
{
    public function fetchAllAction(RequestInterface $request, Model\Product $model): ResponseInterface
    {
        $result = $model->getAll()->toArray();

        return new Response\JsonResponse($result);
    }
}
