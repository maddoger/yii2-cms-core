<?php

namespace maddoger\core\components;

use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;

class KeyStorage extends Component
{
    /**
     * @var bool
     */
    public $autoload = false;

    /**
     * @var string cache key prefix
     */
    public $cachePrefix = '_key_storage_';

    /**
     * @var int
     */
    public $cacheDuration = 60;

    /**
     * @var string item model class
     */
    public $itemModelClass = 'maddoger\\core\\models\KeyStorageItem';

    /**
     * @var array
     */
    private $_values = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->autoload) {
            $this->getAll();
        }
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        $cacheKey = $this->getCacheKey($key);
        if (isset($this->_values[$key])) {
            return $this->_values[$key];
        } elseif ($value = Yii::$app->cache->get($cacheKey)) {
            return $value;
        } else {
            $model = $this->getModel($key);
            if ($model) {

                $this->_values[$key] = $model->value;
                Yii::$app->cache->set($cacheKey, $model->value, $this->cacheDuration);
                return $model->value;

            } else {
                return null;
            }
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function set($key, $value)
    {
        if (!($model = $this->getModel($key))) {
            $model = \Yii::createObject($this->itemModelClass);
        }
        /** @var \maddoger\core\models\KeyStorageItem */
        $model->key = $key;
        $model->value = $value;
        if ($model->save()) {

            $this->_values[$key] = $value;
            Yii::$app->cache->set($this->getCacheKey($key), $value, $this->cacheDuration);
            return true;

        } else {
            return false;
        }
    }

    /**
     * @param string $key
     * @throws \Exception
     */
    public function delete($key)
    {
        call_user_func($this->itemModelClass.'::deleteAll', ['key' => $key]);
        unset($this->_values[$key]);
        Yii::$app->cache->delete($this->getCacheKey($key));
    }

    /**
     * @param $keys
     * @return array
     */
    public function mget($keys)
    {
        $values = [];
        foreach ($keys as $key) {
            $values[$key] = $this->get($key);
        }
        return $values;
    }

    /**
     * @param $items
     * @return array
     */
    public function mset($items)
    {
        $result = true;
        foreach ($items as $key=>$value) {
            $result = $result && $this->set($key, $value);
        }
        return $result;
    }

    /**
     * @param $keys
     */
    public function mdelete($keys)
    {
        call_user_func($this->itemModelClass.'::deleteAll', ['key' => $keys]);
        foreach ($keys as $key) {
            unset($this->_values[$key]);
            Yii::$app->cache->delete($this->getCacheKey($key));
        }
    }

    /**
     * @return array
     */
    public function getAllKeys()
    {
        return $this->getQuery()->select(['key'])->column();
    }

    /**
     * @return array
     */
    public function getAll()
    {
        $values = ArrayHelper::map(
            $this->getQuery()->select(['key', 'value'])->all(),
            'key',
            'value'
        );
        foreach ($values as $key=>$value) {
            $this->_values[$key] = $value;
            Yii::$app->cache->set($this->getCacheKey($key), $value, $this->cacheDuration);
        }
        return $values;
    }

    public function deleteAll()
    {
        $keys = $this->getAllKeys();
        call_user_func($this->itemModelClass.'::deleteAll');
        foreach ($keys as $key) {
            Yii::$app->cache->delete($this->getCacheKey($key));
        }
        $this->_values = [];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getQuery()
    {
        return call_user_func($this->itemModelClass.'::find');
    }

    /**
     * @param $key
     * @return \maddoger\core\models\KeyStorageItem
     */
    public function getModel($key)
    {
        /** @var \yii\db\ActiveQuery $query */
        $query = $this->getQuery();
        return $query->where(['key' => $key])->one();
    }

    /**
     * @param $key
     * @return array
     */
    public function getCacheKey($key)
    {
        return [
            __CLASS__,
            $this->cachePrefix,
            $key
        ];
    }
}