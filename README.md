Oauth2 Yii2 integration
=======================

This extension allow the developper to use [Oauth2](https://bshaffer.github.io/oauth2-server-php-docs/) server.


Installation
------------

If you use Packagist for installing packages, then you can update your composer.json like this :

``` json
{
    "require": {
        "sweelix/yii2-oauth2-server": "*"
    }
}
```

Howto use it
------------

Add extension to your configuration

``` php
return [
    //....
    'bootstrap' => [
        //....
        'oauth2',
        //....
    ],
    'modules' => [
        //....
        'oauth2' => [
            'class' => 'sweelix\oauth2\server\Module',
            'backend' => 'redis',
            'identityClass' => 'app\models\User', // only if you don't want to use the user identityClass
        ],
        //....
    ],
    //....
];
```
