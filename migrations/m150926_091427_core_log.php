<?php

use yii\db\Migration;

class m150926_091427_core_log extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%core_log}}', [
            'id' => $this->bigPrimaryKey(),
            'type' => $this->string(20)->notNull(),
            'title' => $this->string()->notNull(),
            'message' => $this->string(),
            'data' => $this->text(),
            'created_at' => $this->integer()->notNull(),
            'created_by' => $this->integer(),
        ], $tableOptions);

        $this->createIndex($this->db->tablePrefix .'core_log_created_at_ix', '{{%core_log}}', 'created_at');
    }

    public function safeDown()
    {
        $this->dropIndex($this->db->tablePrefix .'core_log_created_at_ix', '{{%core_log}}');
        $this->dropTable('{{%core_log}}');
    }
}
