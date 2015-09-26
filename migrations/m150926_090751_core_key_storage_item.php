<?php

use yii\db\Migration;

class m150926_090751_core_key_storage_item extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%core_key_storage_item}}', [
            'key' => $this->string()->notNull(),
            'value' => $this->text()->notNull(),
            'created_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_at' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $tableOptions);

        $this->addPrimaryKey($this->db->tablePrefix .'core_key_storage_item_pk', '{{%core_key_storage_item}}', 'key');
    }

    public function safeDown()
    {
        $this->dropPrimaryKey($this->db->tablePrefix .'core_key_storage_item_pk', '{{%core_key_storage_item}}');
        $this->dropTable('{{%core_key_storage_item}}');
    }
}
