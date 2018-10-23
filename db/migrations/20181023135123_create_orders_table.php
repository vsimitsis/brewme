<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateOrdersTable extends AbstractMigration
{
    public function change()
    {
        $this->table('orders', ['engine' => 'InnoDB', 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'])
            ->addColumn('user_id',           'integer',  ['limit' => MysqlAdapter::INT_REGULAR, 'null' => false])
            ->addColumn('type',              'string',   ['limit' => 25, 'null' => false])
            ->addColumn('comments',          'string',   ['limit' => 255, 'null' => false])
            ->addColumn('status',            'integer',  ['limit' => MysqlAdapter::INT_TINY, 'null' => false, 'signed' => false])
            ->addTimestamps()
            ->addForeignKey('user_id', 'users', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
            ->save();
    }
}
