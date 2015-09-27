<?php
/**
 * @copyright Copyright (c) 2015 Vitaliy Syrchikov
 * @link http://syrchikov.name
 */

namespace maddoger\core\behaviors;

use Yii;
use yii\base\Behavior;
use yii\base\Exception;
use yii\log\Logger;

/**
 * ConfigurationModelBehavior
 *
 * On attach - load configuration model from key-value storage
 *
 * @author Vitaliy Syrchikov <maddoger@gmail.com>
 * @link http://syrchikov.name
 * @package maddoger/yii2-core
 */
class ConfigurationModelBehavior extends Behavior
{
    /**
     * @var bool
     */
    public $autoload = true;

    /**
     * @var null
     */
    public $modelClass = null;

    /**
     * @var array name => default value
     */
    public $attributes = null;

    /**
     * @var string key storage component id
     */
    public $keyStorage = 'keyStorage';

    /**
     * @var \maddoger\core\components\KeyStorage
     */
    private $_keyStorage = null;

    /**
     * @var \yii\base\Model
     */
    public $_configuration = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (!$this->keyStorage || !($this->_keyStorage = Yii::$app->get($this->keyStorage))) {
            throw new Exception('KeyStorage must be set and exists.');
        }
    }

    /**
     * @inheritdoc
     */
    public function attach($owner)
    {
        parent::attach($owner);
        if ($this->autoload) {
            $this->getConfiguration();
        }
    }

    /**
     * Returns owner config
     * @return \yii\base\Model
     */
    public function getConfiguration() //conflict
    {
        if ($this->_configuration === null) {

            $this->_configuration = $this->_keyStorage->get($this->owner->className().'+'.$this->modelClass) ?: [];
            $modelClass = $this->modelClass;
            if (!$this->_configuration || !($this->_configuration instanceof $modelClass)) {
                //Initialize new model. Default values must be set in model (init method)
                $this->_configuration = Yii::createObject($modelClass);
            }

            if (is_array($this->attributes)) {
                foreach ($this->attributes as $key=>$default) {
                    if ($this->_configuration->{$key} === null) {
                        $this->_configuration->{$key} = $default;
                    }
                }
            }
        }

        return $this->_configuration;
    }

    /**
     * Save configuration model
     * @param bool $validation model validation before saving
     * @return bool
     */
    public function saveConfiguration($validation = true)
    {
        if (!$this->_configuration) {
            return false;
        }

        if ($validation) {
            if (!$this->_configuration->validate()) {
                return false;
            }
        }
        return $this->_keyStorage->set($this->owner->className().'+'.$this->modelClass, $this->_configuration);
    }
}