<?php

use yii\db\Migration;

class m170723_082634_env_vars extends Migration
{
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $tableName = 'env_vars';
        $tableSchema = \Yii::$app->db->schema->getTableSchema($tableName);
        if ($tableSchema === null) {
            $this->createTable($tableName, [
                'key_id' => 'varchar(255) NOT NULL DEFAULT \'\'',
                'value' => 'varchar(255) NOT NULL DEFAULT \'\'',
            ], $tableOptions);
            $this->addPrimaryKey('key_id', $tableName, 'key_id');
        }
    }

    public function down()
    {
        $this->dropTable('env_vars');
    }
}
