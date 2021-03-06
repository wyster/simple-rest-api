<?php declare(strict_types=1);

namespace App\Db\Seeds;

use App\Enum\Status;
use App\Service\Auth\FakeIdentity;
use Exception;
use Phinx\Seed\AbstractSeed;

final class Orders extends AbstractSeed
{
    public function getDependencies()
    {
        return [
            Products::class
        ];
    }

    public function run(): void
    {
        // @todo нужно создавать так же связи в product_orders
        throw new Exception('Not fully implemented!');
        $data = [];
        for ($i = 0; $i < 5; $i++) {
            $ids = array_column(
                $this->adapter->fetchAll('select id from product order by random() limit 5'),
                'id'
            );
            $data[] = [
                'user_id' => (new FakeIdentity())->getId(),
                'products' => json_encode($ids),
                'status' => Status::UNKNOWN()
            ];
        }

        $this->table('order')->insert($data)->save();
    }
}
