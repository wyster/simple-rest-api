<?php declare(strict_types=1);

namespace App\Controller;

use App\Enum\Status;
use App\Exception\Order\OrderNotCreatedDomainException;
use App\Exception\Order\OrderRequestInvalidDomainException;
use App\Service\Auth\IdentityInterface;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use App\Model;
use App\Service;
use App\Entity;
use App\Validator;
use Zend\Hydrator\HydratorInterface;

class OrderController
{
    public function createAction(
        ServerRequestInterface $request,
        HydratorInterface $hydrator,
        Model\Order $model,
        IdentityInterface $identity,
        Validator\Order $validator
    ): ResponseInterface {
        $data = $request->getParsedBody();
        if (!$validator->isValid($data)) {
            throw OrderRequestInvalidDomainException::create(['validator' => $validator->getMessages()]);
        }
        $order = new Entity\Order();
        $hydrator->hydrate($data, $order);
        $order->setUserId($identity->getId());
        $order->setStatus(Status::UNKNOWN());

        if (!$model->create($order)) {
            throw OrderNotCreatedDomainException::create();
        }

        return new Response\JsonResponse(['id' => $order->getId()], StatusCodeInterface::STATUS_CREATED);
    }

    public function payAction(
        ServerRequestInterface $request,
        HydratorInterface $hydrator,
        Service\Order\OrderService $orderService,
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
