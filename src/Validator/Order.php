<?php declare(strict_types=1);

namespace App\Validator;

use Zend\Validator;
use App\Model;

class Order extends AbstractValidator
{
    public function __construct(Model\Product $modelOrder)
    {
        $this->addField(
            [
                'name' => 'products',
                'required' => true,
                'filters' => [],
                'validators' => [
                    new Validator\Callback([
                        'callback' => function ($data) {
                            return is_array($data);
                        },
                        'message' => 'Must be array'
                    ]),
                    new Validator\Callback([
                        'callback' => function (array $ids) {
                            foreach ($ids as $id) {
                                if (!filter_var($id, FILTER_VALIDATE_INT)) {
                                    return false;
                                }
                            }
                            return true;
                        },
                        'message' => 'Array must have integer values'
                    ]),
                    new Validator\Callback([
                        'callback' => function (array $ids) use ($modelOrder) {
                            return $modelOrder->isAllIdsExists($ids);
                        },
                        'message' => 'One or more products not found in db'
                    ]),
                ],
            ]
        );
    }
}
