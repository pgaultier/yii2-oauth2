Oauth2 Yii2 Change Log
======================

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
