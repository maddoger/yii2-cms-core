<?php

namespace maddoger\core\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\helpers\Json;

/**
 * Behavior for packing several attributes to one. Helps when some records may have different properties.
 * For example: Document -> Passport; Driver`s license and others.
 *
 * ```php
 * [
 *  'class' => PackAttributeBehavior::className(),
 *  'attributes' => [
 *      'data' => ['series', 'number', 'date', 'department],
 *  ],
 *  'pack' => PackAttributeBehavior::JSON_PACK,
 *  'unpack' => PackAttributeBehavior::JSON_UNPACK,
 * ],
 * ```
 * @package common\components
 */
class PackAttributeBehavior extends Behavior
{
    const JSON_PACK = 'json';
    const JSON_UNPACK = 'json';

    const SERIALIZE_PACK = 'serialize';
    const SERIALIZE_UNPACK = 'serialize';

    /**
     * @var array
     *
     * ```php
     * [
     *  'data' => ['series', 'number', 'date', 'department']
     * ]
     * ```
     */
    public $attributes;

    /**
     * @var \Closure this function will be used for packing result attribute to string.
     *
     * ```php
     * function ($attribute, $value)
     * {
     *     // return value will be assigned to the attribute
     * }
     * ```
     */
    public $pack = null;

    /**
     * @var \Closure this function will be used for unpacking result attribute from string.
     *
     * ```php
     * function ($attribute, $value)
     * {
     *     // return value will be assigned to the attribute
     * }
     * ```
     */
    public $unpack = null;

    /**
     * @var bool
     */
    public $nullWhenError = true;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_FIND => 'afterFind',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterFind',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterFind',
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
        ];
    }

    /**
     * @param \yii\base\Event $event
     */
    public function afterFind($event)
    {
        if ($this->attributes) {
            foreach ($this->attributes as $toAttribute => $from) {

                $value = $this->owner->{$toAttribute};
                if ($value) {
                    $value = $this->unpack($toAttribute, $value);
                } else {
                    $value = null;
                }

                $this->owner->{$toAttribute} = $value;

                if ($value) {
                    //Unpack all attributes to owner properties
                    foreach ($from as $attr) {
                        $this->owner->{$attr} = $value[$attr];
                    }
                }
            }
        }
    }

    /**
     * @param \yii\base\Event $event
     */
    public function beforeSave($event)
    {
        if ($this->attributes) {
            foreach ($this->attributes as $toAttribute => $from) {

                //Pack all attributes to an array
                $value = [];
                foreach ($from as $attr) {
                    $value[$attr] = $this->owner->{$attr};
                }

                if ($value) {
                    $value = $this->pack($toAttribute, $value);
                } else {
                    $value = null;
                }
                $this->owner->{$toAttribute} = $value;
            }
        }
    }

    /**
     * @param $attribute
     * @param $value
     * @return mixed|null|string
     */
    protected function pack($attribute, $value)
    {
        try {
            if ($this->pack instanceof \Closure) {
                return call_user_func($this->pack, $attribute, $value);
            } elseif ($this->pack === self::JSON_PACK) {
                return Json::encode($value);
            } elseif ($this->pack === self::SERIALIZE_PACK) {
                return serialize($value);
            }
        } catch (\Exception $e) {
            if ($this->nullWhenError) {
                return null;
            }
        }
        return $value;
    }

    /**
     * @param $attribute
     * @param $value
     * @return mixed|null|string
     */
    protected function unpack($attribute, $value)
    {
        try {
            if ($this->unpack instanceof \Closure) {
                return call_user_func($this->unpack, $attribute, $value);
            } elseif ($this->unpack === self::JSON_UNPACK) {
                return Json::decode($value);
            } elseif ($this->unpack === self::SERIALIZE_UNPACK) {
                return unserialize($value);
            }
        } catch (\Exception $e) {
            if ($this->nullWhenError) {
                return null;
            }
        }
        return $value;
    }
}