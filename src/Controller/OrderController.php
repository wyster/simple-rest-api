<?php declare(strict_types=1);

namespace App\Controller;

use App\Enum\Status;
use App\Exception\Order\OrderNotCreatedDomainException;
use App\Service\Auth\IdentityInterface;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use App\Model;
use App\Service;
use App\Entity;
use Zend\Hydrator\HydratorInterface;

class OrderController
{
    public function createAction(
        ServerRequestInterface $request,
        HydratorInterface $hydrator,
        Model\Order $model,
        IdentityInterface $auth
    ): ResponseInterface {
        // @todo валидация
        $order = new Entity\Order();
        $hydrator->hydrate($request->getParsedBody(), $order);
        $order->setUserId($auth->getId());
        $order->setStatus(Status::UNKNOWN());

        if (!$model->create($order)) {
            throw OrderNotCreatedDomainException::create();
        }

        return new Response\JsonResponse(['id' => $order->getId()], StatusCodeInterface::STATUS_CREATED);
    }

    public function payAction(
        ServerRequestInterface $request,
        HydratorInterface $hydrator,
        Service\Order\OrderService $orderService
    ): ResponseInterface {
        // @todo валидация
        $orderPay = new Entity\OrderPay();
        $hydrator->hydrate($request->getParsedBody(), $orderPay);

        $orderService->pay($orderPay);

        return new Response\JsonResponse([]);
    }
}
