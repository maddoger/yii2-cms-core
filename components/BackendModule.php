<?php
/**
 * @copyright Copyright (c) 2015 Vitaliy Syrchikov
 * @link http://syrchikov.name
 */

namespace maddoger\core\components;

use Yii;
use yii\base\Module as BaseModule;

/**
 * BackendModule
 *
 * @author Vitaliy Syrchikov <maddoger@gmail.com>
 * @link http://syrchikov.name
 * @package maddoger/yii2-core
 *
 */
class BackendModule extends BaseModule
{
    /**
     * Number for sorting in backend navigation
     *
     * @var integer
     */
    public $sortNumber = null;

    /**
     * @var bool
     */
    public $showNavigation = true;

    /**
     * @var array real navigation
     */
    private $_navigation;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->registerTranslations();
        $this->ensureBehaviors();
    }

    /**
     * @return string|null Module name
     */
    public function getName()
    {
        return null;
    }

    /**
     * @return string|null Module description
     */
    public function getDescription()
    {
        return null;
    }

    /**
     * @return string|null Module version
     */
    public function getVersion()
    {
        return null;
    }

    /**
     * @return string|null Icon
     */
    public function getIconClass()
    {
        return null;
    }

    /**
     * Rules needed for administrator
     *
     * Operations must be set before using in roles.
     *
     * @return array|null
     */
    public function getRbacItems()
    {
        return null;
    }

    /**
     * Returns navigation items for backend
     * @return array
     */
    public function getNavigation()
    {
        if ($this->_navigation) {
            return $this->_navigation;
        } else {
            return $this->getDefaultNavigation();
        }
    }

    /**
     * Replaces default navigation
     * @param $value
     */
    public function setNavigation($value)
    {
        $this->_navigation = $value;
    }

    /**
     * Returns module`s default navigation items for backend
     *
     * @return array
     */
    public function getDefaultNavigation()
    {
        return null;
    }

    /**
     * @return array
     */
    public function getSearchSources()
    {
        return null;
    }

    /**
     * Calls after module initialization
     */
    public function registerTranslations()
    {

    }
}