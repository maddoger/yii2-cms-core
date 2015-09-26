<?php

namespace maddoger\core\models;

use maddoger\core\behaviors\SerializeAttributeBehavior;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%core_key_storage_item}}".
 *
 * @property string $key
 * @property mixed $value
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 */
class KeyStorageItem extends ActiveRecord
{
    /**
     * Prefix for cache key
     */
    const CACHE_PREFIX = '_key_storage_';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%core_key_storage_item}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['key', 'value'], 'required'],
            [['created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['key'], 'string', 'max' => 255],
            ['value', 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => SerializeAttributeBehavior::className(),
                'attributes' => ['value'],
            ],
            TimestampBehavior::className(),
            BlameableBehavior::className(),
        ];
    }
}
