<?php

/**
 * @copyright Copyright (c) 2015 Vitaliy Syrchikov
 * @link http://syrchikov.name
 */

namespace maddoger\core\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;

class SerializeAttributeBehavior extends Behavior
{
    /**
     * @var array attributes names for serializing
     */
    public $attributes = null;

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
            foreach ($this->attributes as $attribute) {
                if ($this->owner->{$attribute}) {
                    try {
                        $this->owner->{$attribute} = unserialize($this->owner->{$attribute});
                    } catch (\Exception $e) {
                        if ($this->nullWhenError) {
                            $this->owner->{$attribute} = null;
                        }
                    }
                } else {
                    $this->owner->{$attribute} = null;
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
            foreach ($this->attributes as $attribute) {
                if ($this->owner->{$attribute}) {
                    try {
                        $this->owner->{$attribute} = serialize($this->owner->{$attribute});
                    } catch (\Exception $e) {
                        if ($this->nullWhenError) {
                            $this->owner->{$attribute} = null;
                        }
                    }
                } else {
                    $this->owner->{$attribute} = null;
                }
            }
        }
    }
}