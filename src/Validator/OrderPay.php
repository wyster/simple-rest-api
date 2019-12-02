<?php declare(strict_types=1);

namespace App\Validator;

use App\Service\Auth\IdentityInterface;
use Zend\Validator;
use App\Model;
use App\Entity;

class OrderPay extends AbstractValidator
{
    /**
     * @var Model\Order
     */
    private $modelOrder;
    /**
     * @var IdentityInterface
     */
    private $identity;

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
                    ]),
                    new Validator\Callback([
                        'callback' => function (int $id) use ($modelOrder) {
                            return $modelOrder->getById($id) === null;
                        },
                        'message' => 'Order not found in db'
                    ]),
                ],
            ]
        );
    }
}
