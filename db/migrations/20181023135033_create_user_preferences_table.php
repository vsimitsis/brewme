<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateUserPreferencesTable extends AbstractMigration
{
    public function change()
    {
        $this->table('user_preferences', ['engine' => 'InnoDB', 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'])
            ->addColumn('user_id',           'integer',   ['limit' => MysqlAdapter::INT_REGULAR, 'null' => false, 'signed' => false])
            ->addColumn('type',              'string',   ['limit' => 25, 'null' => false])
            ->addColumn('comments',          'string',   ['limit' => 255, 'null' => false])
            ->addIndex(['user_id', 'type'], ['unique' => true, 'name' => 'idx_user_id_type'])
            ->addForeignKey('user_id', 'users', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
            ->save(); 
    }
}
