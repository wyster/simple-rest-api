<?php declare(strict_types=1);

namespace App\Controller;

use App\Exception\Order\OrderRequestInvalidDomainException;
use App\Service\Order\OrderService;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response;
use App\Entity;
use App\Validator;
use Laminas\Hydrator\HydratorInterface;

final class OrderController
{
    public function createAction(
        ServerRequestInterface $request,
        HydratorInterface $hydrator,
        OrderService $orderService,
        Validator\Order $validator
    ): ResponseInterface {
        $data = $request->getParsedBody();
        if (!$validator->isValid($data)) {
            throw OrderRequestInvalidDomainException::create(['validator' => $validator->getMessages()]);
        }

        $order = new Entity\Order();
        $hydrator->hydrate($data, $order);

        $orderService->create($order);

        return new Response\JsonResponse(['id' => $order->getId()], StatusCodeInterface::STATUS_CREATED);
    }

    public function payAction(
        ServerRequestInterface $request,
        HydratorInterface $hydrator,
        OrderService $orderService,
        Validator\OrderPay $validator
    ): ResponseInterface {
        $data = $request->getParsedBody();
        if (!$validator->isValid($data)) {
            throw OrderRequestInvalidDomainException::create(['validator' => $validator->getMessages()]);
        }
        $orderPay = new Entity\OrderPay();
        $hydrator->hydrate($data, $orderPay);

        $orderService->pay($orderPay);

        return new Response\JsonResponse([]);
    }
}
