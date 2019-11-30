<?php declare(strict_types=1);

namespace App\Controller;

use App\Enum\Status;
use Fig\Http\Message\StatusCodeInterface;
use Lcobucci\ContentNegotiation\UnformattedResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use App\Model;
use App\Entity;
use Zend\Hydrator\HydratorInterface;

class OrderController
{
    public function createAction(ServerRequestInterface $request, HydratorInterface $hydrator, Model\Order $model): ResponseInterface
    {
        $order = new Entity\Order();
        $hydrator->hydrate($request->getParsedBody(), $order);
        $order->setUserId(2);
        $order->setStatus(Status::UNKNOWN());

        $model->create($order);

        $response = (new Response())
            ->withStatus(StatusCodeInterface::STATUS_OK)
            ->withHeader('Content-type', 'application/json');
        return new UnformattedResponse(
            $response,
            $hydrator->extract($order)
        );
    }

    public function payAction(ServerRequestInterface $request, HydratorInterface $hydrator, Model\Order $model): ResponseInterface
    {
        $order = new Entity\OrderPay();
        $hydrator->hydrate($request->getParsedBody(), $order);

        if ($model->getById($order->getId()) === null) {
            return (new Response())
                ->withStatus(StatusCodeInterface::STATUS_NOT_FOUND);
        }

        $response = (new Response())
            ->withStatus(StatusCodeInterface::STATUS_OK)
            ->withHeader('Content-type', 'application/json');
        return new UnformattedResponse(
            $response,
            $hydrator->extract($order)
        );
    }
}
