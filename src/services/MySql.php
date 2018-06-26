<?php
/**
 * MySql.php
 *
 * PHP version 5.6+
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\services
 */

namespace sweelix\oauth2\server\services;

use sweelix\oauth2\server\interfaces\ServiceBootstrapInterface;
use sweelix\oauth2\server\services\mySql\AccessTokenService;
use sweelix\oauth2\server\services\mySql\AuthCodeService;
use sweelix\oauth2\server\services\mySql\ClientService;
use sweelix\oauth2\server\services\mySql\CypherKeyService;
use sweelix\oauth2\server\services\mySql\JtiService;
use sweelix\oauth2\server\services\mySql\JwtService;
use sweelix\oauth2\server\services\mySql\RefreshTokenService;
use sweelix\oauth2\server\services\mySql\ScopeService;
use Yii;

/**
 * This is the service loader for mySql
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\services
 * @since 1.0.0
 */
class MySql implements ServiceBootstrapInterface
{
    /**
     * @inheritdoc
     */
    public static function register($app)
    {
        if (Yii::$container->hasSingleton('sweelix\oauth2\server\interfaces\AccessTokenServiceInterface') === false) {
            Yii::$container->setSingleton('sweelix\oauth2\server\interfaces\AccessTokenServiceInterface', [
                'class' => AccessTokenService::class,
                'accessTokensTable' => 'oauthAccessTokens',
                'scopeAccessTokenTable' => 'oauthScopeAccessToken'
            ]);
        }
        if (Yii::$container->hasSingleton('sweelix\oauth2\server\interfaces\AuthCodeServiceInterface') === false) {
            Yii::$container->setSingleton('sweelix\oauth2\server\interfaces\AuthCodeServiceInterface', [
                'class' => AuthCodeService::class,
                'authorizationCodesTable' => 'oauthAuthorizationCodes',
                'scopeAuthorizationCodeTable' => 'oauthScopeAuthorizationCode',
            ]);
        }
        if (Yii::$container->hasSingleton('sweelix\oauth2\server\interfaces\ClientServiceInterface') === false) {
            Yii::$container->setSingleton('sweelix\oauth2\server\interfaces\ClientServiceInterface', [
                'class' => ClientService::class,
                'clientGrantTypeTable' => 'oauthClientGrantType',
                'clientsTable' => 'oauthClients',
                'clientUserTable' => 'oauthClientUser',
                'scopeClientTable' => 'oauthScopeClient',
            ]);
        }
        if (Yii::$container->hasSingleton('sweelix\oauth2\server\interfaces\CypherKeyServiceInterface') === false) {
            Yii::$container->setSingleton('sweelix\oauth2\server\interfaces\CypherKeyServiceInterface', [
                'class' => CypherKeyService::class,
                'cypherKeysTable' => 'oauthCypherKeys',
            ]);
        }
        if (Yii::$container->hasSingleton('sweelix\oauth2\server\interfaces\JtiServiceInterface') === false) {
            Yii::$container->setSingleton('sweelix\oauth2\server\interfaces\JtiServiceInterface', [
                'class' => JtiService::class,
                'jtisTable' => 'oauthJtis',
            ]);
        }
        if (Yii::$container->hasSingleton('sweelix\oauth2\server\interfaces\JwtServiceInterface') === false) {
            Yii::$container->setSingleton('sweelix\oauth2\server\interfaces\JwtServiceInterface', [
                'class' => JwtService::class,
                'jwtsTable' => 'oauthJwts',
            ]);
        }
        if (Yii::$container->hasSingleton('sweelix\oauth2\server\interfaces\RefreshTokenServiceInterface') === false) {
            Yii::$container->setSingleton('sweelix\oauth2\server\interfaces\RefreshTokenServiceInterface', [
                'class' => RefreshTokenService::class,
                'refreshTokensTable' => 'oauthRefreshTokens',
                'scopeRefreshTokenTable' => 'oauthScopeRefreshToken'
            ]);
        }
        if (Yii::$container->hasSingleton('sweelix\oauth2\server\interfaces\ScopeServiceInterface') === false) {
            Yii::$container->setSingleton('sweelix\oauth2\server\interfaces\ScopeServiceInterface', [
                'class' => ScopeService::class,
                'scopeClientTable' => 'oauthScopeClient',
                'scopesTable' => 'oauthScopes',
            ]);
        }
    }
}
