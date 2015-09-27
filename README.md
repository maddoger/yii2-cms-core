Yii2 Core Module by maddoger

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist maddoger/yii2-cms-core "*"
```

or add

```
"maddoger/yii2-cms-core": "*"
```

to the require section of your `composer.json` file.


Logging to DB
-------------

```
'log' => [
    'traceLevel' => YII_DEBUG ? 3 : 0,
    'targets' => [

        'db' => [
            'class' => 'yii\log\DbTarget',
            'levels' => ['error', 'warning'],
            'except'=>['yii\web\HttpException:*', 'yii\i18n\I18N\*'],
            'prefix'=>function () {
                $url = !Yii::$app->request->isConsoleRequest ? Yii::$app->request->getUrl() : null;
                return sprintf('[%s][%s]', Yii::$app->id, $url);
            },
            'logTable' => '{{%core_log}}',
        ],
    ],
],
```