<?php
/**
 * @copyright Copyright (c) 2015 Vitaliy Syrchikov
 * @link http://syrchikov.name
 */

namespace maddoger\core\models;

use Yii;
use yii\base\Model;

/**
 * Config Model
 *
 * @author Vitaliy Syrchikov <maddoger@gmail.com>
 * @link http://syrchikov.name
 */
class ConfigurationModel extends Model
{
    /**
     * @var string key of
     */
    public $key;

    /**
     * @var string
     */
    private $_formName;

    /**
     * @inheritdoc
     */
    public function __sleep()
    {
        //Save only attributes
        return $this->attributes();
    }

    /**
     * Set dynamic form name
     * @param string $value
     */
    public function setFormName($value)
    {
        $this->_formName = $value;
    }

    /**
     * @return string
     */
    public function formName()
    {
        return $this->_formName ?: parent::formName();
    }
}
