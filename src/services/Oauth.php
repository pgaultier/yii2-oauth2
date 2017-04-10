<?php
/**
 * Oauth.php
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

use OAuth2\GrantType\AuthorizationCode;
use OAuth2\GrantType\ClientCredentials;
use OAuth2\GrantType\JwtBearer;
use OAuth2\GrantType\RefreshToken;
use OAuth2\GrantType\UserCredentials;
use OAuth2\Server;
use sweelix\oauth2\server\interfaces\ServiceBootstrapInterface;
use sweelix\oauth2\server\Module;
use yii\helpers\Url;
use Yii;

/**
 * This is the service loader for bshaffer oauth2 elements
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\services
 * @since 1.0.0
 */
class Oauth implements ServiceBootstrapInterface
{
    /**
     * @inheritdoc
     */
    public static function register($app)
    {
        $module = Module::getInstance();
        Yii::$container->set('OAuth2\Server', function($container, $params, $config) use ($module) {
            $storage = Yii::createObject('sweelix\oauth2\server\storage\OauthStorage');
            $server = new Server($storage, self::prepareServerConfig($config, $module));
            return $server;
        });

        Yii::$container->set('OAuth2\GrantType\AuthorizationCode', function($container, $params, $config) {
            $storage = Yii::createObject('sweelix\oauth2\server\storage\OauthStorage');
            $grantType = new AuthorizationCode($storage);
            return $grantType;
        });

        Yii::$container->set('OAuth2\GrantType\ClientCredentials', function($container, $params, $config) use ($module) {
            $storage = Yii::createObject('sweelix\oauth2\server\storage\OauthStorage');
            $grantType = new ClientCredentials($storage, self::prepareServerConfig($config, $module));
            return $grantType;
        });

        Yii::$container->set('OAuth2\GrantType\JwtBearer', function($container, $params, $config) use ($module) {
            $storage = Yii::createObject('sweelix\oauth2\server\storage\OauthStorage');
            $audience = Url::to($module->jwtAudience, true);
            $grantType = new JwtBearer($storage, $audience, null, self::prepareServerConfig($config, $module));
            return $grantType;
        });

        Yii::$container->set('OAuth2\GrantType\RefreshToken', function($container, $params, $config) use ($module) {
            $storage = Yii::createObject('sweelix\oauth2\server\storage\OauthStorage');
            $grantType = new RefreshToken($storage, self::prepareServerConfig($config, $module));
            return $grantType;
        });

        Yii::$container->set('OAuth2\GrantType\UserCredentials', function($container, $params, $config) {
            $storage = Yii::createObject('sweelix\oauth2\server\storage\OauthStorage');
            $grantType = new UserCredentials($storage);
            return $grantType;
        });

    }

    /**
     * @param mixed $config
     * @return array Oauth server configuration
     * @since 1.0.0
     */
    protected static function prepareServerConfig($config, $module)
    {
        $baseConfig = [
            'use_jwt_access_tokens' => $module->allowJwtAccessToken,
            'store_encrypted_token_string' => $module->storeEncryptedTokenString,
            'use_openid_connect' => $module->allowOpenIdConnect,
            'id_lifetime' => $module->idTTL,
            'access_lifetime' => $module->accessTokenTTL,
            'refresh_token_lifetime' => $module->refreshTokenTTL,
            'www_realm' => $module->realm,
            'token_param_name' => $module->tokenQueryName,
            'token_bearer_header_name' => $module->tokenBearerName,
            'enforce_state' => $module->enforceState,
            'require_exact_redirect_uri' => $module->allowOnlyRedirectUri,
            'allow_implicit' => $module->allowImplicit,
            'allow_credentials_in_request_body' => $module->allowCredentialsInRequestBody,
            'allow_public_clients' => $module->allowPublicClients,
            'always_issue_new_refresh_token' => $module->alwaysIssueNewRefreshToken,
            'unset_refresh_token_after_use' => $module->unsetRefreshTokenAfterUse,
            'enforce_redirect' => $module->enforceRedirect,
            'auth_code_lifetime' => $module->authorizationCodeTTL,
        ];
        if (is_array($config) === false) {
            $config = [];
        }
        return array_merge($baseConfig, $config);
    }
}
