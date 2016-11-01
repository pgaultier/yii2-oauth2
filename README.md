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
            //
            // Parameters
            //
        ],
        //....
    ],
    //....
];
```

Module Parameters
-----------------

### Basic module parameters

 * `backend` : can only be **redis** for the moment
 * `identityClass` : user class used to link oauth2 authorization system default to user component `identityClass`
 * `baseEndPoint` : base path for token and authorize endpoints default to `''`
    * Token endpoint https://host.xxx/token
    * Authorize endpoint https://host.xxx/authorize

### JWT parameters

 * `allowJwtAccesToken` : enable JWT (default : **false**)
 * `allowAlgorithm` : available algorithm for JWT (default : **['RS256', 'RS384', 'RS512']**)
 * `jwtAudience` : default to token endpoint
 * `storeEncryptedTokenString` : store encrypted token (default : **true**)

### OpenID

 * `allowOpenIdConnect` : enable openId connect (default : **false**) // not implemented yet

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

### Grants management 
 
 * `allowImplicit` : allow implicit grant (default to **false**)
 * `allowCredentialsInRequestBody` : allow credentials in request body (default to **true**)
 * `allowPublicClients` : allow public clients (default to **true**)
 * `alwaysIssueNewRefreshToken` : always issue refresh token (default to **true**)
 * `unsetRefreshTokenAfterUse` : unset refresh token after use (default to **true**) 

User identity link
------------------

Configure the user component to link oauth2 system and user / identity management

``` php
return [
    //....
    'components' => [
        //....
        'user' => [
            'class' => 'sweelix\oauth2\server\web\User',
            'identityClass' => 'app\models\User', // Identity clas must implement UserModelInterface
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

 * `Yii::$app->user->can('read')` will check if
    1. will check if scope `read` is allowed for current client
    2. will check if rbac permission `read` is allowed for current user 
 
 * `Yii::$app->user->can('rbac:read')` will check if
    1. will **not** check if scope `read` is allowed for current client
    2. will check if rbac permission `read` is allowed for current user 

 * `Yii::$app->user->can('oauth2:read')` will check if
    1. will check if scope `read` is allowed for current client
    2. will **not** check if rbac permission `read` is allowed for current user 

CLI System
----------

Several commands are available to manage oauth2 system

 * `php protected/yii.php oauth2:client/create`
 * `php protected/yii.php oauth2:key/create`
 * `php protected/yii.php oauth2:scope/create`
