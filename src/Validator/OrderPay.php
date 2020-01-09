<?php declare(strict_types=1);

namespace App\Validator;

use App\Service\Auth\IdentityInterface;
use Laminas\Validator;
use App\Model;
use App\Entity;

final class OrderPay extends AbstractValidator
{
    /**
     * @var Model\Order
     */
    private Model\Order $modelOrder;
    /**
     * @var IdentityInterface
     */
    private IdentityInterface $identity;

    public function __construct(Model\Order $modelOrder, IdentityInterface $identity)
    {
        $this->modelOrder = $modelOrder;
        $this->identity = $identity;

        $this->addField(
            [
                'name' => 'id',
                'required' => true,
                'validators' => [
                    new Validator\Callback([
                        'callback' => function ($id) {
                            return filter_var($id, FILTER_VALIDATE_INT);
                        },
                        'message' => 'Must be integer'
                    ]),
                    new Validator\Callback([
                        'callback' => function (int $id) {
                            $order = $this->modelOrder->getById($id);
                            if (!$order instanceof Entity\Order) {
                                return false;
                            }

                            if ($order->getUserId() !== $this->identity->getId()) {
                                return false;
                            }

                            return true;
                        },
                        'message' => 'Order not found in db or access denied'
                    ]),
                ],
            ]
        );

        $this->addField(
            [
                'name' => 'amount',
                'required' => true,
                'validators' => [
                    new Validator\Callback([
                        'callback' => function ($id) {
                            return filter_var($id, FILTER_VALIDATE_INT);
                        },
                        'message' => 'Must be integer'
                    ])
                ],
            ]
        );
    }
}
