Oauth2 Yii2 Change Log
======================


 * Chg: remove testing support for `HHVM` and php `5.6`
 * Chg: update dependencies
 * Enh: adding `.gitattributes`
 
1.2.0 April 10, 2017
--------------------

 * Chg: Update `fxp-asset`
 * Chg: update dependencies
 * Enh: Adding new methods `findAllByUserId()` and `findAllByClientId()` for models `AccessToken` and `RefreshToken`
 * Enh: Adding new methods `hasUser()`, `addUser()`, `removeUser()` and `findAllByUserId()` from model `Client`
 * Enh: Allow oauth2 `user` to be fully decoupled from app `user` 
 * Fix: Fix Jwt token
 * Enh: Enable redis automatic expire for `AccessToken`, `RefreshToken` and `AuthorizationCode`

1.1.0 January 02, 2017
----------------------

 * Chg: update dependencies
 * Enh: Adding `CORS` support for `token` endpoint
 * Enh: Allow `HttpBearerAuth` and `QueryParamAuth` for method `findIdentityByAccessToken` 

1.0.3 December 16, 2016
-----------------------

 * Enh: Allow multiple redirectUri creation in `oauth2:client/create` use `,` as separator
 * Fix: Correct Array conversion in `oauth2:client/create`
 * Fix: Remove userId type check
 * Fix: fix `allowJwtAccessToken` typo (was `allowJwtAccesToken`)
 * Enh: update composer.json to check openssl ext
 * Fix: Fix badges in readme
 * Enh: adding multiple redirectUri support.

1.0.2 December 6, 2016
----------------------

 * Chg: update dependencies
 * Enh: adding gitlab ci

1.0.1 November 7, 2016
----------------------

 * Fix: add missing view / layout override in module conf

1.0.0 November 7, 2016
----------------------

 * Fix: use yii2-redis 2.0.4 because 2.0.5 cannot reopen connection after closing it
 * Chg: Initial public release
