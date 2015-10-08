<?php
/**
 * @copyright Copyright (c) 2015 Vitaliy Syrchikov
 * @link http://syrchikov.name
 */

namespace maddoger\core\behaviors;

use Yii;
use yii\base\Behavior;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\log\Logger;

/**
 * ConfigurationBehavior
 *
 * On attach - load configuration, apply it
 *
 * @author Vitaliy Syrchikov <maddoger@gmail.com>
 * @link http://syrchikov.name
 * @package maddoger/yii2-core
 */
class ConfigurationBehavior extends Behavior
{
    /**
     * @var bool
     */
    public $autoLoad = true;

    /**
     * @var array name => default value
     */
    public $attributes = null;

    /**
     * @var bool if true all attributes will be written in owner properties
     * otherwise configuration model/array will be available through getConfiguration()
     */
    public $saveToOwnerProperties = false;

    /**
     * @var bool don't save attributes with default values, only changed
     */
    public $ignoreIfDefault = true;

    /**
     * @var string path to the configuration view
     * example: $this->getViewPath() . DIRECTORY_SEPARATOR . 'configuration.php'
     */
    public $view;

    /**
     * @var array required user RBAC roles
     * If user has at least one role
     */
    public $roles;

    /**
     * @var string|array configuration model class
     * example: maddoger\admin\model\Configuration.php
     */
    public $modelClass;

    /**
     * @var array Dynamic model properties, if modelClass is not use
     * Example:
     * 'dynamicModel' => [
     *      //'class' => 'maddoger\core\models\DynamicModel'
     *      'formName' => $this->id,
     *      'attributes' => [
     *          'logoText' => $this->logoText,
     *          'logoImageUrl' => $this->logoImageUrl,
     *          'sortNumber' => $this->sortNumber,
     *       ],
     *       'rules' => [
     *           [['logoText', 'logoImageUrl'], 'string'],
     *           [['logoText', 'logoImageUrl', 'sortNumber'], 'default', ['value' => null]],
     *       ],
     *   ]
     */
    public $dynamicModel;

    /**
     * @var string key storage component id
     */
    public $keyStorage = 'keyStorage';

    /**
     * @var \maddoger\core\components\KeyStorage
     */
    private $_keyStorage = null;

    /**
     * @var string key
     */
    private $_key;

    /**
     * @var mixed
     */
    private $_configuration;

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
        if ($this->autoLoad) {
            $this->configure();
        }
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->_key ?: $this->owner->className();
    }

    /**
     * @param string $value
     */
    public function setKey($value)
    {
        $this->_key = $value;
    }

    /**
     * Returns owner config
     * @return mixed
     */
    public function getConfiguration()
    {
        if (!$this->_configuration) {

            //If model is using
            if ($this->modelClass) {

                $this->_configuration = $this->_keyStorage->get($this->getKey()) ?: [];
                $modelClass = $this->modelClass;
                if (!$this->_configuration || !($this->_configuration instanceof $modelClass)) {
                    //Initialize new model. Default values must be set in model (init method)
                    $this->_configuration = Yii::createObject($modelClass);
                }

                //Default values from 'attributes'
                if (is_array($this->attributes)) {
                    foreach ($this->attributes as $key => $default) {
                        if ($this->_configuration->{$key} === null) {
                            $this->_configuration->{$key} = $default;
                        }
                    }
                }

            } else {

                //Array configuration
                $dbConfig = $this->_keyStorage->get($this->getKey()) ?: [];

                if (is_array($this->attributes)) {
                    //Needs to filter
                    $config = [];
                    foreach ($this->attributes as $key => $default) {
                        $config[$key] = (isset($dbConfig[$key]) && $dbConfig[$key] !== null) ? $dbConfig[$key] : $default;
                    }
                    $this->_configuration = $config;
                } else {
                    $this->_configuration = $dbConfig;
                }

            }
        }
        return $this->_configuration;
    }

    /**
     * Configure owner
     */
    public function configure()
    {
        $config = $this->getConfiguration();
        if ($config) {
            Yii::getLogger()->log('CONFIGURATION_BEHAVIOR - '.$this->owner->className(), Logger::LEVEL_INFO);

            if ($this->saveToOwnerProperties) {
                if (method_exists($config, 'getAttributes')) {
                    Yii::configure($this->owner, $config->getAttributes());
                } else {
                    Yii::configure($this->owner, $config);
                }
            }
        }
    }

    /**
     * @return \yii\base\Model|null
     */
    public function getConfigurationModel()
    {
        if ($this->modelClass) {
            return $this->getConfiguration();
        } elseif (!empty($this->dynamicModel)) {

            $modelConfig = $this->dynamicModel;
            $class = ArrayHelper::getValue($modelConfig, 'class', 'maddoger\\core\\models\\DynamicModel');
            $attributes = ArrayHelper::remove($modelConfig, 'attributes', $this->attributes);
            $rules = ArrayHelper::remove($modelConfig, 'rules', []);

            $model = Yii::createObject($class, $modelConfig);
            if ($model) {
                if ($attributes) {
                    foreach ($attributes as $key=>$value) {
                        $model->defineAttribute($key, $value);
                    }
                }
                if ($rules) {
                    foreach ($rules as $rule) {
                        $model->addRule($rule[0], $rule[1], isset($rule[2]) ? $rule[2] : []);
                    }
                }

                //Set current values
                $model->setAttributes($this->getConfiguration(), false);

                return $model;
            } else {
                return null;
            }

        } else {
            return null;
        }
    }

    /**
     * Saves configuration model. Performs validation if needed.
     *
     * @param \yii\base\Model $model
     * @param bool $validate
     * @param bool $apply
     * @return bool
     */
    public function saveConfigurationModel($model, $validate = true, $apply = true)
    {
        if (!$model) {
            return false;
        }
        if ($validate && !$model->validate()) {
            return false;
        }
        if ($this->modelClass) {
            //If modelClass is using
            $modelClass = $this->modelClass;
            //Check object and save
            if (
                ($model instanceof $modelClass) &&
                $this->_keyStorage->set($this->getKey(), $model)
            ) {
                //Apply new configuration
                if ($apply) {
                    $this->_configuration = $model;
                    $this->configure();
                }
                return true;
            } else {
                return false;
            }
        } else {
            return $this->saveConfigurationArray($model->getAttributes(), $apply);
        }
    }

    /**
     * Save array config
     * @param mixed $config
     * @param bool $apply
     * @return bool
     */
    public function saveConfigurationArray($config, $apply = true)
    {
        if ($this->ignoreIfDefault && $this->attributes) {
            foreach ($this->attributes as $key=>$default) {
                if (isset($config[$key]) && $config[$key]==$default) {
                    unset($config[$key]);
                }
            }
        }
        if ($this->_keyStorage->set($this->getKey(), $config))
        {
            if ($apply) {
                $this->_configuration = null;
                $this->configure();
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return mixed
     */
    public function getConfigurationView()
    {
       return ($this->view && file_exists($this->view)) ? $this->view : null;
    }

    /**
     * @return array
     */
    public function getConfigurationRoles()
    {
        return $this->roles;
    }

    /**
     * @return array
     */
    protected function getPropertiesFromOwner()
    {
        $ownerConfig = [];
        if ($this->attributes) {
            foreach ($this->attributes as $key=>$default) {
                $ownerConfig[$key] = $this->owner->{$key};
            }
        }
        return $ownerConfig;
    }
}