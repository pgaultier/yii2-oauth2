Oauth2 Yii2 integration
=======================

This extension allow the developper to use [Oauth2](https://bshaffer.github.io/oauth2-server-php-docs/) server.

[![Latest Stable Version](https://poser.pugx.org/sweelix/yii2-oauth2-server/v/stable)](https://packagist.org/packages/sweelix/yii2-oauth2-server)
[![Build Status](https://api.travis-ci.org/pgaultier/yii2-oauth2.svg?branch=master)](https://travis-ci.org/pgaultier/yii2-oauth2)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/pgaultier/yii2-oauth2/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/pgaultier/yii2-oauth2/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/pgaultier/yii2-oauth2/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/pgaultier/yii2-oauth2/?branch=master)
[![License](https://poser.pugx.org/sweelix/yii2-oauth2-server/license)](https://packagist.org/packages/sweelix/yii2-oauth2-server)

[![Latest Development Version](https://img.shields.io/badge/unstable-devel-yellowgreen.svg)](https://packagist.org/packages/sweelix/yii2-oauth2-server)
[![Build Status](https://travis-ci.org/pgaultier/yii2-oauth2.svg?branch=devel)](https://travis-ci.org/pgaultier/yii2-oauth2)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/pgaultier/yii2-oauth2/badges/quality-score.png?b=devel)](https://scrutinizer-ci.com/g/pgaultier/yii2-oauth2/?branch=devel)
[![Code Coverage](https://scrutinizer-ci.com/g/pgaultier/yii2-oauth2/badges/coverage.png?b=devel)](https://scrutinizer-ci.com/g/pgaultier/yii2-oauth2/?branch=devel)
[![composer.lock](https://poser.pugx.org/sweelix/yii2-oauth2-server/composerlock)](https://packagist.org/packages/sweelix/yii2-oauth2-server)

Installation
------------

If you use Packagist for installing packages, then you can update your composer.json like this :

``` json
{
    "require": {
        "sweelix/yii2-oauth2-server": "~1.2.0"
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
            'db' => 'redis',
            'identityClass' => 'app\models\User', // only if you don't want to use the user identityClass
            //
            // Parameters
            //
        ],
        //....
    ],
    //....
];
```

Configure Module
----------------

### Basic module parameters

 * `backend` : can only be **redis** for the moment
 * `db` : id of the redis component or connection or connection configuration
 * `identityClass` : user class used to link oauth2 authorization system default to user component `identityClass`
 * `webUserParamId` : allow separation between main app user (session) and module app user, (default to **__oauth2**)
 * `identityCookieName` : allow separation between main app user (cookie) and module app user, (default to **oauth2**)
 * `webUser` : allow full management of module web user, (default to **[]**)
 * `baseEndPoint` : base path for token and authorize endpoints default to `''`
    * Token endpoint https://host.xxx/token
    * Authorize endpoint https://host.xxx/authorize
 * `overrideLayout` : override module layout to use another one (ex: @app/views/layouts/oauth2)
 * `overrideViewPath` : override view path to use specific one (ex: @app/views/oauth2)   

### Grants management 
 
 * `allowImplicit` : allow implicit grant (default to **false**)
 * `allowAuthorizationCode` : allow authorization code grant (default to **true**)
 * `allowClientCredentials` : allow client credentials grant (default to **true**)
 * `allowPassword` : allow user credentials / password grant (default to **true**)
 * `allowCredentialsInRequestBody` : allow credentials in request body (default to **true**)
 * `allowPublicClients` : allow public clients (default to **true**)
 * `alwaysIssueNewRefreshToken` : always issue refresh token (default to **true**)
 * `unsetRefreshTokenAfterUse` : unset refresh token after use (default to **true**) 

### JWT parameters

 * `allowJwtAccessToken` : enable JWT (default : **false**)
 * `allowAlgorithm` : available algorithm for JWT (default : **['RS256', 'RS384', 'RS512']**)
 * `jwtAudience` : default to token endpoint
 * `storeEncryptedTokenString` : store encrypted token (default : **true**)

### Time To Live

 * `idTTL` : TTL of ID Token (default to **3600**)
 * `accessTokenTTL` : TTL of access token (default to **3600**)
 * `refreshTokenTTL` : TTL of refresh token (default to **14 * 24 * 3600**)

### Basic Oauth names

 * `realm` : Realm value (default to **Service**)
 * `tokenQueryName` : name of the access token parameter (default to **access_token**)
 * `tokenBearerName` : name of authorization header (default to **Bearer**)

### Enforce parameters
 
 * `enforceState` : enforce state parameter (default to **true**)
 * `allowOnlyRedirectUri` : need exact redirect URI (default to **true**)

### OpenID

 * `allowOpenIdConnect` : enable openId connect (default : **false**) // not implemented yet

### Authorization Code parameters

 * `enforceRedirect` : enforce redirect parameter (default to **false**)
 * `authorizationCodeTTL` : TTL of authorization code (default to **30**)

### CORS

 * `cors` : enable `CORS` on the token endpoint (default : **false**) the CORS part can be defined using an array as described [in Yii documentation](http://www.yiiframework.com/doc-2.0/yii-filters-cors.html)
 
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
             'db' => 'redis',
             'identityClass' => 'app\models\User', // only if you don't want to use the user identityClass
             //
             // Cors parameters example :
             //
             'cors' => [
                'Origin' => ['https://www.myowndomain.com'],
             ]
         ],
         //....
     ],
     //....
 ];

```
 
User identity and Web user
--------------------------

Configure the user component to link oauth2 system and user / identity management

``` php
return [
    //....
    'components' => [
        //....
        'user' => [
            'class' => 'sweelix\oauth2\server\web\User',
            'identityClass' => 'app\models\User', // Identity class must implement UserModelInterface
            //
            // Parameters
            //
        ],
        //....
    ],
    //....
];
```

`IdentityClass` must implements `sweelix\oauth2\server\interfaces\UserModelInterface`. You can use the trait
`sweelix\oauth2\server\traits\IdentityTrait` to automagically implement 

 * `public function getRestrictedScopes()`
 * `public function setRestrictedScopes($scopes)`
 * `public static function findIdentityByAccessToken($token, $type = null)`

you will have to implement the remaining methods : 

 * `public static function findByUsernameAndPassword($username, $password)`
 * `public static function findByUsername($username)`

Creating specific view for OAuth2
---------------------------------

In order to use your own views (instead of the builtin ones), you can override 
 * `layout` : module parameter `overrideLayout`
 * `viewPath` : module parameter `overrideViewPath`
 
### Overriding layout
 
You should create a classic layout like :
 
```php
<?php
/**
 * @app/views/layouts/newLayout.php
 * @var string $content
 */
use yii\helpers\Html;

$this->beginPage(); ?>
    <!DOCTYPE html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title><?php echo Html::encode($this->title); ?></title>

        <meta name="viewport" content="width=device-width, initial-scale=1">

        <?php $this->head(); ?>
    </head>
    <body>
        <?php $this->beginBody(); ?>
            <?php echo $content;?>
        <?php $this->endBody(); ?>
    </body>

</html>
<?php $this->endPage();

``` 

and link it to the module

```php
return [
    //....
    'modules' => [
        //....
        'oauth2' => [
            'class' => 'sweelix\oauth2\server\Module',
            'overrideLayout' => '@app/views/layouts/newLayout',
            //
            // Additional Parameters
            //
        ],
        //....
    ],
    //....
];
```

### Overriding views
 
You should create 3 views to allow oauth2 module to work as expected and link them to the module

```php
return [
    //....
    'modules' => [
        //....
        'oauth2' => [
            'class' => 'sweelix\oauth2\server\Module',
            // use views in folder oauth2
            'overrideViewPath' => '@app/views/oauth2',
            //
            // Additional Parameters
            //
        ],
        //....
    ],
    //....
];
```
 
#### Error view
 
This view is used to display a page when an error occurs
 
```php
<?php
/**
 * error.php
 *
 * @var string $type error type
 * @var string $description error description
 */
use yii\helpers\Html;
?>

    <h1 class="alert-heading"><?php echo ($type ? : 'Unkown error'); ?></h1>
    <p><?php echo ($description ? : 'Please check your request'); ?></p>

``` 


#### Login view
 
This view is used to display a login page when needed
 
```php
<?php
/**
 * login.php
 *
 * @var \sweelix\oauth2\server\forms\User $user
 *
 */
use yii\helpers\Html;
?>
    <?php echo Html::beginForm('', 'post', ['novalidate' => 'novalidate']); ?>
        <label>Username</label>
        <?php echo Html::activeTextInput($user, 'username', [
            'required' => 'required',
        ]); ?>
        <br/>
    
        <label>Password</label>
        <?php echo Html::activePasswordInput($user, 'password', [
            'required' => 'required',
        ]); ?>
        <br/>
        <button type="submit">LOGIN</button>
    <?php echo Html::endForm(); ?>

``` 

#### Authorize view
 
This view is used to display an authorization page when needed
 
```php
<?php
/**
 * authorize.php
 *
 * @var \sweelix\oauth2\server\interfaces\ScopeModelInterface[] $requestedScopes
 * @var \sweelix\oauth2\server\interfaces\ClientModelInterface $client
 *
 */
use yii\helpers\Html;
?>
    <h1><?php echo $client->name ?> <span>requests access</span></h1>
    
    <?php echo Html::beginForm(); ?>
        <?php if(empty($requestedScopes) === false) : ?>
        <ul>
            <?php foreach($requestedScopes as $scope): ?>
            <li>
                <h4><?php echo $scope->id; ?></h4>
                <p>
                    <?php echo $scope->definition; ?>
                </p>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
            <!-- name of decline button **must be** decline -->
            <button type="submit" name="decline">DECLINE</button>
            <!-- name of accept button **must be** accept -->
            <button type="submit" name="accept">AUTHORIZE</button>
    <?php echo Html::endForm(); ?>

``` 

Exposed Models overview
-----------------------

The Oauth2 Yii2 extension expose severall models which can be used in your application.
All models can be overloaded using Yii2 DI.

For example, if you want to overload the `Client` model, you have to inject your own model in the DI using:

```php

Yii::$container->set('sweelix\oauth2\server\interfaces\ClientModelInterface', [
    'class' => YourClientModel::className(),
]);
```

### Client / ClientModelInterface

 * `Client::findOne($id)` - Find client by ID
 * `Client::findAllByUserId($id)` - Find all clients accepted by user (userId)
 * `$client->save()` - Save client
 * `$client->delete()` - Delete client
 * `$client->hasUser($userId)` - Check if user (userId) has accepted the client
 * `$client->addUser($userId)` - Attach the user (userId) to the client
 * `$client->removeUser($userId)` - Dettach the user (userId) from the client
 
### AccessToken / AccessTokenModelInterface

 * `AccessToken::findOne($id)` - Find accessToken by ID
 * `AccessToken::findAllByUserId($id)` - Find all accessTokens for user (userId)
 * `AccessToken::findAllByClientId($id)` - Find all accessTokens for client (clientId)
 * `AccessToken::deleteAllByUserId($id)` - Delete all accessTokens for user (userId)
 * `AccessToken::deleteAllByClientId($id)` - Delete all accessTokens for client (clientId)
 * `$accessToken->save()` - Save accessToken
 * `$accessToken->delete()` - Delete accessToken

### RefreshToken / RefreshTokenModelInterface

 * `RefreshToken::findOne($id)` - Find accessToken by ID
 * `RefreshToken::findAllByUserId($id)` - Find all refreshTokens for user (userId)
 * `RefreshToken::findAllByClientId($id)` - Find all refreshTokens for client (clientId)
 * `RefreshToken::deleteAllByUserId($id)` - Delete all refreshTokens for user (userId)
 * `RefreshToken::deleteAllByClientId($id)` - Delete all refreshTokens for client (clientId)
 * `$refreshToken->save()` - Save refreshToken
 * `$refreshToken->delete()` - Delete refreshToken

### AuthCode / AuthCodeModelInterface

 * `AuthCode::findOne($id)` - Find authCode by ID
 * `$authCode->save()` - Save authCode
 * `$authCode->delete()` - Delete authCode

### Scope / ScopeModelInterface

 * `Scope::findOne($id)` - Find scope by ID
 * `Scope::findAvailableScopeIds()` - Find all scopes IDs
 * `Scope::findDefaultScopeIds()` - Find default scopes IDs
 * `$scope->save()` - Save scope
 * `$scope->delete()` - Delete scope

### CypherKey / CypherKeyModelInterface

 * `CypherKey::findOne($id)` - Find cypherKey by ID
 * `$cypherKey->save()` - Save cypherKey
 * `$cypherKey->delete()` - Delete cypherKey
 * `$cypherKey->generateKeys()` - Generate random keys for current cypherKey

Linking RBAC and Scope systems
------------------------------

Using `sweelix\oauth2\server\web\User` class will automagically link `rbac` system and `oauth2` system.

Permission system will be slightly modified to allow fine grained checks :

 * `Yii::$app->user->can('read')` will check
    1. if scope `read` is allowed for current client
    2. if rbac permission `read` is allowed for current user 
 
 * `Yii::$app->user->can('rbac:read')` will check **only** if rbac permission `read` is allowed for current user 

 * `Yii::$app->user->can('oauth2:read')` will check **only** if scope `read` is allowed for current client

Running the tests
-----------------

Before running the tests, you should edit the file tests/config/redis.php and change the config to match your environment.

CLI System
----------

Several commands are available to manage oauth2 system

 * `php protected/yii.php oauth2:client/create`
 * `php protected/yii.php oauth2:client/update`
 * `php protected/yii.php oauth2:key/create`
 * `php protected/yii.php oauth2:scope/create`
