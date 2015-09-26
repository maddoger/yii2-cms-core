<?php
/**
 * @copyright Copyright (c) 2015 Vitaliy Syrchikov
 * @link http://syrchikov.name
 */

namespace maddoger\models;

use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * Config Model
 *
 * Model for saving configuration.
 *
 * Model needs for form and easy access to attributes.
 *
 * @author Vitaliy Syrchikov <maddoger@gmail.com>
 * @link http://syrchikov.name
 */
class ConfigModel extends Model
{
    /**
     * @var
     */
    public $objectClass;

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
     * Save config
     * @return bool
     */
    public function save()
    {
        $objectClass = $this->objectClass;
        return $this->_keyStorage->set($objectClass, $this->attributes());
    }

    /**
     * Returns owner config
     * @param string $objectClass
     * @param array $defaults
     * @param string $containerClass
     * @return static
     */
    public static function getConfig($objectClass, $defaults = [], $containerClass = 'maddoger\core\models\Config')
    {
        $obj = $containerClass::getConfig($objectClass);
        $thisClass = static::className();
        if (!$obj || !($obj instanceof $thisClass)) {
            $obj = Yii::createObject($thisClass);
            $obj->setAttributes($defaults);
            $obj->objectClass = $objectClass;
            $obj->containerClass = $containerClass;
        }
        //Set default values
        foreach ($defaults as $key => $value) {
            if (!$obj->{$key} || empty($obj->{$key})) {
                $obj->{$key} = $value;
            }
        }
        return $obj;
    }
}