Oauth2 Yii2 integration
=======================

This extension allow the developper to use [Oauth2](https://oauth2.thephpleague.com/) server.


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

Prepare ```public.key``` 

``` bash
openssl genrsa -out private.key 1024
```

or with passphrase

``` bash
openssl genrsa -passout pass:_passphrase_ -out private.key 1024
```

and ```private.key```

```bash
openssl rsa -in private.key -pubout -out public.key
```

or with passphrase

``` bash
openssl rsa -in private.key -passin pass:_passphrase_ -pubout -out public.key
```

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
            'privateKey' => '@app/config/private-dev.key',
            'publicKey' => '@app/config/public-dev.key',
            // 'passphrase' => 'xxx', // only if passphrase has been defined
            'user' => 'app\models\User',
        ],
        //....
    ],
    //....
];
```
