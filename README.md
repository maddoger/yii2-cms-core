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

Configuration
-------------

Configurations in modules
    - properties
    - models (frontend & backend)

For usage:
    - loading
    - reading interface (model or property)

For editing:
    - view
    - model
    - load/save interface

ConfigurationBehavior

Universal behavior.
```
'configurationBehavior' => [
    'class' => ConfigurationBehavior::className(),
    'key' => $this->id.'_custom', //owner class by default

    //Reading
    'attributes' => [
        //Default values
        'logoText' => $this->logoText,
        'logoImageUrl' => $this->logoImageUrl,
        'sortNumber' => $this->sortNumber,
    ],
    'saveToOwnerProperties' => true, // if true all attributes will be written in owner properties
                                     // otherwise configuration model/array will be available through getConfiguration()

    //Editing
    'view' => $this->getViewPath() . DIRECTORY_SEPARATOR . 'configuration.php',
    //Model for user
    'modelClass' => 'maddoger\admin\model\Configuration.php',
    //OR
    'dynamicModel' => [
        'formName' => $this->id,
        'attributes' => [
            'logoText' => $this->logoText,
            'logoImageUrl' => $this->logoImageUrl,
            'sortNumber' => $this->sortNumber,
        ],
        'rules' => [
            [['logoText', 'logoImageUrl'], 'string'],
            [['logoText', 'logoImageUrl', 'sortNumber'], 'default', ['value' => null]],
        ],
    ]
]
```