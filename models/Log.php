<?php

namespace maddoger\core\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Exception;

/**
 * Log message model
 *
 * @property integer $id
 * @property integer $level
 * @property string $category
 * @property double $log_time
 * @property string $prefix
 * @property string $message
 *
 */
class Log extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%core_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['level'], 'integer'],
            [['log_time'], 'number'],
            [['prefix', 'message'], 'string'],
            [['category'], 'string', 'max' => 255]
        ];
    }

    /**
     * @param int $startTime default value is -1 week
     * @return \yii\db\ActiveQuery
     */
    public static function findLastMessages($startTime = null)
    {
        if ($startTime === null) {
            $startTime = strtotime('-1 week');
        }
        return static::find()->where(['>', 'log_time', $startTime])->orderBy(['log_time' => SORT_DESC]);
    }

    /**
     * Flush all table data using `truncate` (resets auto-increment)
     * @return int
     * @throws Exception
     * @throws \Exception
     */
    public static function flushAll()
    {
        try {
            return static::getDb()->createCommand()->truncateTable(static::tableName())->execute();
        } catch (Exception $e) {
            if (YII_ENV_DEV) {
                throw $e;
            }
        }
    }
}
