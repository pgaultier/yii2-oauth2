Oauth2 Yii2 integration
=======================

This extension allow the developper to use [Oauth2](https://bshaffer.github.io/oauth2-server-php-docs/) server.

[![Latest Stable Version](https://poser.pugx.org/sweelix/yii2-oauth2/v/stable)](https://packagist.org/packages/sweelix/yii2-oauth2)
[![Build Status](https://api.travis-ci.org/pgaultier/yii2-oauth2.svg?branch=master)](https://travis-ci.org/pgaultier/yii2-oauth2)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/pgaultier/yii2-oauth2/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/pgaultier/yii2-oauth2/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/pgaultier/yii2-oauth2/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/pgaultier/yii2-oauth2/?branch=master)
[![License](https://poser.pugx.org/sweelix/yii2-oauth2/license)](https://packagist.org/packages/sweelix/yii2-oauth2)

[![Latest Development Version](https://img.shields.io/badge/unstable-devel-yellowgreen.svg)](https://packagist.org/packages/sweelix/yii2-oauth2)
[![Build Status](https://travis-ci.org/pgaultier/yii2-oauth2.svg?branch=devel)](https://travis-ci.org/pgaultier/yii2-oauth2)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/pgaultier/yii2-oauth2/badges/quality-score.png?b=devel)](https://scrutinizer-ci.com/g/pgaultier/yii2-oauth2/?branch=devel)
[![Code Coverage](https://scrutinizer-ci.com/g/pgaultier/yii2-oauth2/badges/coverage.png?b=devel)](https://scrutinizer-ci.com/g/pgaultier/yii2-oauth2/?branch=devel)

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
 * `identityClass` : user class used to link oauth2 authorization system default to user component `identityClass`
 * `baseEndPoint` : base path for token and authorize endpoints default to `''`
    * Token endpoint https://host.xxx/token
    * Authorize endpoint https://host.xxx/authorize

### Grants management 
 
 * `allowImplicit` : allow implicit grant (default to **false**)
 * `allowAuthorizationCode` : allow authorization code grant (default to **true**)
 * `allowClientCredentials` : allow client credentials grant (default to **true**)
 * `allowPassword` : allow user credentials / password grant (default to **true**)
 * `allowRefreshToken` : allow refresh token grant (default to **true**)
 * `allowCredentialsInRequestBody` : allow credentials in request body (default to **true**)
 * `allowPublicClients` : allow public clients (default to **true**)
 * `alwaysIssueNewRefreshToken` : always issue refresh token (default to **true**)
 * `unsetRefreshTokenAfterUse` : unset refresh token after use (default to **true**) 

### JWT parameters

 * `allowJwtAccesToken` : enable JWT (default : **false**)
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

Linking RBAC and Scope systems
------------------------------

Using `sweelix\oauth2\server\web\User` class will automagically link `rbac` system and `oauth2` system.

Permission system will be slightly modified to allow fine grained checks :

 * `Yii::$app->user->can('read')` will check
    1. if scope `read` is allowed for current client
    2. if rbac permission `read` is allowed for current user 
 
 * `Yii::$app->user->can('rbac:read')` will check **only** if rbac permission `read` is allowed for current user 

 * `Yii::$app->user->can('oauth2:read')` will check **only** if scope `read` is allowed for current client

CLI System
----------

Several commands are available to manage oauth2 system

 * `php protected/yii.php oauth2:client/create`
 * `php protected/yii.php oauth2:key/create`
 * `php protected/yii.php oauth2:scope/create`
