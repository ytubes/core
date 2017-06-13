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
		}

        $tableName = 'settings';
        $tableSchema = \Yii::$app->db->schema->getTableSchema($tableName);

        if ($tableSchema === null) {
	        $this->createTable($tableName, [
	            'module' => 'varchar(32) NOT NULL',
	            'name' => 'varchar(32) NOT NULL',
	            'value' => 'varchar(255) NOT NULL DEFAULT \'\'',
	        ], $tableOptions);

	        $this->addPrimaryKey('module', $tableName, ['module', 'name']);

        	$this->batchInsert($tableName, ['module', 'name', 'value'], [
        		['', 'site_url', 'http://site.com'],
        		['', 'site_title', 'Название сайта'],
        		['', 'site_description', 'Мета описание для сайта'],
        		['', 'site_enable', '1'],
        		['', 'access_admin_area', '1'],
        		['', 'support_email', 'support@email.com'],
        		['', 'abuse_email', 'abuse@email.com'],
        	]);
		}
    }

    public function down()
    {
        echo "m170612_140356_base_init cannot be reverted.\n";

        return false;
    }

}
