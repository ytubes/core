<?php

use yii\db\Migration;

class m170612_140356_base_init extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $tableName = 'users';
        $tableSchema = \Yii::$app->db->schema->getTableSchema($tableName);

        if ($tableSchema === null) {
            $this->createTable($tableName, [
                'user_id' => 'int(10) unsigned NOT NULL',
                'username' => 'varchar(255) NOT NULL DEFAULT \'\'',
                'auth_key' => 'varchar(255) NOT NULL DEFAULT \'\'',
                'password_hash' => 'varchar(255) NOT NULL DEFAULT \'\'',
                'password_reset_token' => 'varchar(255) NOT NULL DEFAULT \'\'',
                'email' => 'varchar(255) NOT NULL DEFAULT \'\'',
                'status' => 'tinyint(3) unsigned NOT NULL DEFAULT 10',
                'admin' => 'tinyint(3) unsigned NOT NULL DEFAULT 0',
                'last_seen' => 'timestamp NULL DEFAULT NULL',
                'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
                'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            ], $tableOptions);

            $this->addPrimaryKey('user_id', $tableName, 'user_id');
            $this->execute("ALTER TABLE `{$tableName}` MODIFY `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT");
            $this->createIndex('username', $tableName, 'username', true);
            $this->createIndex('password_reset_token', $tableName, 'password_reset_token');
            $this->createIndex('last_seen', $tableName, 'last_seen');

            $this->batchInsert($tableName, ['user_id', 'username', 'auth_key', 'password_hash', 'email', 'status', 'admin', 'created_at', 'updated_at'], [
                [1, 'razrab', 'Gb1Dacm-9QcnMd0bfK7VpEFFCmwq_03O', '$2y$13$48dv3HUUSUw80g8YboVJj.q3MA.J3br1FcjD36.9EM91Eg6rdVvAm', '', 10, 1, gmdate('Y-m-d H:i:s'), gmdate('Y-m-d H:i:s')],
                [2, 'AD.x', 'SrYEsdGt8kobo4UBU4XdV8ail6ffgd_0', '$2y$13$J/fSqz5Qs1iBnVnOCOG0pOaC4Cz1eqsd7J.F7ZK/ppkVN9VpZGWxq', 'adx.informer@gmial.com', 10, 1, gmdate('Y-m-d H:i:s'), gmdate('Y-m-d H:i:s')],
            ]);
        }

        $tableName = 'settings';
        $tableSchema = \Yii::$app->db->schema->getTableSchema($tableName);

        if ($tableSchema === null) {
            $this->createTable($tableName, [
                'module_id' => 'varchar(32) NOT NULL',
                'name' => 'varchar(32) NOT NULL',
                'value' => 'varchar(255) NOT NULL DEFAULT \'\'',
            ], $tableOptions);

            $this->addPrimaryKey('module_id', $tableName, ['module_id', 'name']);

            $this->batchInsert($tableName, ['module', 'name', 'value'], [
                ['base', 'site_url', 'http://site.com'],
                ['base', 'site_title', 'Название сайта'],
                ['base', 'site_description', 'Мета описание для сайта'],
                ['base', 'site_enable', '1'],
                ['base', 'access_admin_area', '1'],
                ['base', 'support_email', 'support@email.com'],
                ['base', 'abuse_email', 'abuse@email.com'],
            ]);
        }
    }

    public function down()
    {
        echo "m170612_140356_base_init cannot be reverted.\n";

        return false;
    }

}
