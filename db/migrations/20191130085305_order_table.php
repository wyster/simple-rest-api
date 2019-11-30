<?php declare(strict_types=1);

namespace App\Db\Migrations;

use Phinx\Db\Adapter\AdapterInterface;
use Phinx\Migration\AbstractMigration;

class OrderTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('order')
            ->addColumn('user', AdapterInterface::PHINX_TYPE_INTEGER)
            ->addColumn('status', AdapterInterface::PHINX_TYPE_INTEGER, ['length' => 1])
            ->addColumn('products', AdapterInterface::PHINX_TYPE_JSON)
            ->save();
    }
}
