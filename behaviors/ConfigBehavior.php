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
 * $module->config[var]?
 * $module->params[var]?
 * $module->var?
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
     * @param array $defaults
     * @return mixed
     */
    public function getConfig($defaults = [])
    {
        $class = $this->owner->className();
        $config = $this->_keyStorage->get($class) ?: [];
        return array_replace($defaults, $config);
    }

    /**
     * Save config
     * @param mixed $config
     * @return bool
     */
    public function setConfig($config)
    {
        $class = $this->owner->className();
        return $this->_keyStorage->set($class, $config);
    }

    /**
     * Configure owner
     */
    public function configure()
    {
        $config = $this->getConfig();
        if ($config) {
            Yii::getLogger()->log('CONFIG_BEHAVIOR_CONFIGURE', Logger::LEVEL_INFO);
            Yii::configure($this->owner, $config);
        }
    }
}