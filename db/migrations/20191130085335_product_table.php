<?php declare(strict_types=1);

namespace App\Db\Migrations;

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\AdapterInterface;

final class ProductTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('product')
            ->addColumn('title', AdapterInterface::PHINX_TYPE_STRING)
            ->addColumn('price', AdapterInterface::PHINX_TYPE_INTEGER)
            ->save();
    }
}
