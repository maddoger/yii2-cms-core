<?php
/**
 * @copyright Copyright (c) 2015 Vitaliy Syrchikov
 * @link http://syrchikov.name
 */

namespace maddoger\core\i18n;

use Yii;
use yii\web\UrlManager;
use yii\web\UrlRule;

/**
 * URL manager for handling language param
 * @package maddoger\core\i18n
 */
class LanguageUrlManager extends UrlManager
{
    /**
     * @var array of available languages of website
     * Example:
     * [
     *  [
     *      'slug' => 'ru',
     *      'locale' => 'ru-RU',
     *      'name' => 'Русский',
     *  ],
     *  [
     *      'slug' => 'en',
     *      'locale' => 'en-US',
     *      'name' => 'English',
     *  ],
     * ]
     */
    public $availableLanguages = [];

    /**
     * Parse request and set application language
     * @param \yii\web\Request $request
     * @return array|bool
     */
    public function parseRequest($request)
    {
        if (count($this->availableLanguages) > 0) {
            if (preg_match('/\/(' . implode('|', $this->availableLanguages) . ')(.*)/si', $request->url, $matches)) {
                Yii::$app->language = $matches[1];
            }
        }

        $res = parent::parseRequest($request);
        if (is_array($res)) {
            if (isset($res[1]['language']) && in_array($res[1]['language'], $this->availableLanguages)) {
                Yii::$app->language = $res[1]['language'];
            }
        }
        return $res;
    }

    /**
     * @param array|string $params
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function createUrl($params)
    {
        $langUnset = false;
        if (!isset($params['language'])) {
            $params['language'] = Yii::$app->language;
            $langUnset = true;
        }

        $params = (array)$params;
        $anchor = isset($params['#']) ? '#' . $params['#'] : '';
        unset($params['#'], $params[$this->routeParam]);

        $route = trim($params[0], '/');
        unset($params[0]);
        $baseUrl = $this->getBaseUrl();

        if ($this->enablePrettyUrl) {
            /** @var UrlRule $rule */
            foreach ($this->rules as $rule) {
                if (($url = $rule->createUrl($this, $route, $params)) !== false) {
                    if ($rule->host !== null) {
                        if ($baseUrl !== '' && ($pos = strpos($url, '/', 8)) !== false) {
                            return substr($url, 0, $pos) . $baseUrl . substr($url, $pos);
                        } else {
                            return $url . $baseUrl . $anchor;
                        }
                    } else {
                        return "$baseUrl/{$url}{$anchor}";
                    }
                }
            }

            if ($this->suffix !== null) {
                $route .= $this->suffix;
            }
            if (!empty($params)) {
                $route .= '?' . http_build_query($params);
            }
            return "$baseUrl/{$route}{$anchor}";
        } else {
            $url = "$baseUrl?{$this->routeParam}=$route";
            if (!empty($params)) {
                if ($langUnset) unset($params['language']);
                $url .= '&' . http_build_query($params);
            }
            return $url . $anchor;
        }
    }
}