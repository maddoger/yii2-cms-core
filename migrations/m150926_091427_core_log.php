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
            'level' => $this->integer(),
            'category' => $this->string(),
            'log_time' => $this->double(),
            'prefix' => $this->text(),
            'message' => $this->text(),
        ], $tableOptions);

        $this->createIndex($this->db->tablePrefix .'core_log_level_ix', '{{%core_log}}', 'level');
        $this->createIndex($this->db->tablePrefix .'core_log_category_ix', '{{%core_log}}', 'category');
    }

    public function safeDown()
    {
        $this->dropIndex($this->db->tablePrefix .'core_log_category_ix', '{{%core_log}}');
        $this->dropIndex($this->db->tablePrefix .'core_log_level_ix', '{{%core_log}}');
        $this->dropTable('{{%core_log}}');
    }
}
