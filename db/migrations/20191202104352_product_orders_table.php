<?php declare(strict_types=1);

namespace App\Db\Migrations;

use Phinx\Db\Adapter\AdapterInterface;
use Phinx\Migration\AbstractMigration;

class ProductOrdersTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('product_orders')
            ->addColumn('product', AdapterInterface::PHINX_TYPE_INTEGER)
            ->addForeignKey(['product'], 'product')
            ->addColumn('order', AdapterInterface::PHINX_TYPE_INTEGER)
            ->addForeignKey(['order'], 'order')
            ->save();
    }
}
