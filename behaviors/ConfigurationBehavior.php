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
     * @var string key storage component id
     */
    public $keyStorage = 'keyStorage';

    /**
     * @var \maddoger\core\components\KeyStorage
     */
    private $_keyStorage = null;

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
     * Returns owner config
     * @return mixed
     */
    public function getConfiguration()
    {
        $class = $this->owner->className();
        $dbConfig = $this->_keyStorage->get($class) ?: [];

        if (is_array($this->attributes)) {
            //Needs to filter
            $config = [];
            foreach ($this->attributes as $key=>$default) {
                $config[$key] = isset($dbConfig[$key]) ? $dbConfig[$key] : $default;
            }
            return $config;
        } else {
            return $dbConfig;
        }
    }

    /**
     * Save config
     * @param mixed $config
     * @return bool
     */
    public function setConfiguration($config)
    {
        $class = $this->owner->className();
        return $this->_keyStorage->set($class, $config);
    }

    /**
     * Configure owner
     */
    public function configure()
    {
        $config = $this->getConfiguration();
        if ($config) {
            Yii::getLogger()->log('CONFIGURATION_BEHAVIOR - '.$this->owner->className(), Logger::LEVEL_INFO);
            Yii::configure($this->owner, $config);
        }
    }
}