<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateUsersTable extends AbstractMigration
{
    public function change()
    {
        $this->table('users', ['engine' => 'InnoDB', 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'])
            ->addColumn('username',           'string',   ['limit' => 80, 'null' => false])
            ->addIndex(['username'], ['unique' => true])
            ->save();
    }
}
