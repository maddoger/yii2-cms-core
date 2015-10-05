<?php

namespace maddoger\core\models;

use Yii;
use yii\base\Model;

class MultiModel extends Model
{
    /**
     * @var string
     */
    public $db = 'db';

    /**
     * @var bool
     */
    public $transaction = true;

    /**
     * @var Model[]
     */
    protected $_models;

    /**
     * @param $models Model[]
     */
    public function setModels($models)
    {
        foreach ($models as $key=>$model) {
            $this->setModel($key, $model);
        }
    }

    /**
     * @return Model[]
     */
    public function getModels()
    {
        return $this->_models;
    }

    /**
     * @param $key string
     * @param $model Model
     */
    public function setModel($key, $model)
    {
        $this->_models[$key] = $model;
    }

    /**
     * @param $key string
     * @return Model
     */
    public function getModel($key)
    {
        return isset($this->_models[$key]) ? $this->_models[$key] : null;
    }

    /**
     * @inheritdoc
     */
    public function validate($attributeNames = null, $clearErrors = true)
    {
        $result = true;
        if ($this->_models) {
            foreach ($this->_models as $key=>$model) {
                if (!$model->validate(
                    isset($attributeNames[$key]) ? $attributeNames[$key] : null,
                    $clearErrors
                )) {
                    $result = false;
                    $this->addErrors([$key => $model->getErrors()]);
                }
            }
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function load($data, $formName = null)
    {
        $result = true;
        if ($this->_models) {
            foreach ($this->_models as $key=>$model) {
                $result = $result && $model->load($data, $formName);
            }
        }
        return $result;
    }

    /**
     * @param bool|true $runValidation
     * @param bool $transaction
     * @return bool
     * @throws \yii\db\Exception
     */
    public function save($runValidation = true, $transaction = null)
    {
        if ($runValidation && !$this->validate()) {
            return false;
        }
        if ($transaction === null) {
            $transaction = $this->transaction;
        }

        $dbTransaction = $transaction ? $this->getDb()->beginTransaction() : null;

        $result = true;
        foreach ($this->_models as $model) {
            if (!$result) {
                if ($transaction) {
                    $dbTransaction->rollBack();
                }
                return false;
            }
            $result = $model->save(false);
        }
        if ($transaction) {
            $dbTransaction->commit();
        }
        return true;
    }

    /**
     * @return \yii\db\Connection
     * @throws \yii\base\InvalidConfigException
     */
    public function getDb()
    {
        return Yii::$app->get($this->db);
    }
}