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
 *
 * @author Vitaliy Syrchikov <maddoger@gmail.com>
 * @link http://syrchikov.name
 */
class ConfigurationModel extends Model
{
    public function __sleep()
    {
        //Save only attributes
        return $this->attributes();
    }
}