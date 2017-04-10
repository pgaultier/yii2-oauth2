<?php
/**
 * Module.php
 *
 * PHP version 5.6+
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server
 */
namespace sweelix\oauth2\server;

use sweelix\oauth2\server\services\Oauth;
use sweelix\oauth2\server\services\Redis;
use yii\base\BootstrapInterface;
use yii\base\Module as BaseModule;
use yii\console\Application as ConsoleApplication;
use Yii;
use yii\di\Instance;
use yii\helpers\ArrayHelper;

/**
 * Oauth2 server Module definition
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server
 * @since 1.0.0
 */
class Module extends BaseModule implements BootstrapInterface
{
    /**
     * @var string backend to use, available backends are 'redis'
     */
    public $backend;

    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection.
     */
    public $db;

    /**
     * @var string override layout. For example @app/views/layouts/oauth2 to use <app>/views/layouts/oauth2.php layout
     */
    public $overrideLayout;

    /**
     * @var string override view path. For example @app/views/oauth2 to use <app>/views/oauth2/(authorize|login|error) views
     */
    public $overrideViewPath;

    /**
     * This user class will be used to link oauth2 authorization system with the application.
     * The class must implement \sweelix\oauth2\server\interfaces\UserInterface
     * If not defined, the Yii::$app->user->identityClass value will be used
     * @var string|array user class definition.
     */
    public $identityClass;

    /**
     * @var string used to separate user session between this module and current application
     */
    public $webUserParamId = '__oauth2';

    /**
     * @var string used to separate identity cookies between this module and current application
     */
    public $identityCookieName = 'oauth2';

    /**
     * @var array webUser configuration specific to this module
     */
    public $webUser = [];

    /**
     * @var string change base end point
     */
    public $baseEndPoint = '';

    /**
     * @var bool configure oauth server (use_jwt_access_tokens)
     */
    public $allowJwtAccessToken = false;

    /**
     * @var array configure oauth server (allowed_algorithms)
     */
    public $allowAlgorithm = ['RS256', 'RS384', 'RS512'];

    /**
     * @var string|array jwt audience. Default to token endpoint
     */
    public $jwtAudience = ['token/index'];

    /**
     * @var bool configure oauth server (store_encrypted_token_string)
     */
    public $storeEncryptedTokenString = true;

    /**
     * @var bool configure oauth server (use_openid_connect)
     */
    public $allowOpenIdConnect = false;

    /**
     * @var int configure oauth server (id_lifetime)
     */
    public $idTTL = 3600;

    /**
     * @var int configure oauth server (access_lifetime)
     */
    public $accessTokenTTL = 3600;

    /**
     * @var int configure oauth server (refresh_token_lifetime)
     */
    public $refreshTokenTTL = 1209600;

    /**
     * @var string configure oauth server (www_realm)
     */
    public $realm = 'Service';

    /**
     * @var string configure oauth server (token_param_name)
     */
    public $tokenQueryName = 'access_token';

    /**
     * @var string configure oauth server (token_bearer_header_name)
     */
    public $tokenBearerName = 'Bearer';

    /**
     * @var bool configure oauth server (enforce_state)
     */
    public $enforceState = true;

    /**
     * @var bool configure oauth server (require_exact_redirect_uri)
     */
    public $allowOnlyRedirectUri = true;

    /**
     * @var bool configure oauth server (allow_implicit)
     */
    public $allowImplicit = false;

    /**
     * @var bool allow authorization code grant
     */
    public $allowAuthorizationCode = true;

    /**
     * @var bool allow client credentials grant
     */
    public $allowClientCredentials = true;

    /**
     * @var bool allow password grant
     */
    public $allowPassword = true;

    /**
     * @var bool configure oauth server (allow_credentials_in_request_body)
     */
    public $allowCredentialsInRequestBody = true;

    /**
     * @var bool configure oauth server (allow_public_clients)
     */
    public $allowPublicClients = true;

    /**
     * @var bool configure oauth server (always_issue_new_refresh_token)
     */
    public $alwaysIssueNewRefreshToken = true;

    /**
     * @var bool configure oauth server (unset_refresh_token_after_use)
     */
    public $unsetRefreshTokenAfterUse = false;

    /**
     * @var int duration of login time for multiple authorize calls
     */
    public $loginDuration = 60 * 60 * 24 * 30;

    /**
     * @var bool configure authorization code (enforce_redirect)
     */
    public $enforceRedirect = false;

    /**
     * @var int configure authorization code (auth_code_lifetime)
     */
    public $authorizationCodeTTL = 30;

    /**
     * @var false|array Cors configuration if allowed @see http://www.yiiframework.com/doc-2.0/yii-filters-cors.html
     */
    public $cors = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

    /**
     * Load dataservices in container
     * @param \yii\base\Application $app
     * @since 1.0.0
     */
    protected function setUpDi($app)
    {
        if (Yii::$container->has('scope') === false) {
            Yii::$container->set('scope', 'sweelix\oauth2\server\validators\ScopeValidator');
        }
        if (Yii::$container->has('sweelix\oauth2\server\interfaces\AccessTokenModelInterface') === false) {
            Yii::$container->set('sweelix\oauth2\server\interfaces\AccessTokenModelInterface', 'sweelix\oauth2\server\models\AccessToken');
        }
        if (Yii::$container->has('sweelix\oauth2\server\interfaces\AuthCodeModelInterface') === false) {
            Yii::$container->set('sweelix\oauth2\server\interfaces\AuthCodeModelInterface', 'sweelix\oauth2\server\models\AuthCode');
        }
        if (Yii::$container->has('sweelix\oauth2\server\interfaces\ClientModelInterface') === false) {
            Yii::$container->set('sweelix\oauth2\server\interfaces\ClientModelInterface', 'sweelix\oauth2\server\models\Client');
        }
        if (Yii::$container->has('sweelix\oauth2\server\interfaces\CypherKeyModelInterface') === false) {
            Yii::$container->set('sweelix\oauth2\server\interfaces\CypherKeyModelInterface', 'sweelix\oauth2\server\models\CypherKey');
        }
        if (Yii::$container->has('sweelix\oauth2\server\interfaces\JtiModelInterface') === false) {
            Yii::$container->set('sweelix\oauth2\server\interfaces\JtiModelInterface', 'sweelix\oauth2\server\models\Jti');
        }
        if (Yii::$container->has('sweelix\oauth2\server\interfaces\JwtModelInterface') === false) {
            Yii::$container->set('sweelix\oauth2\server\interfaces\JwtModelInterface', 'sweelix\oauth2\server\models\Jwt');
        }
        if (Yii::$container->has('sweelix\oauth2\server\interfaces\RefreshTokenModelInterface') === false) {
            Yii::$container->set('sweelix\oauth2\server\interfaces\RefreshTokenModelInterface', 'sweelix\oauth2\server\models\RefreshToken');
        }
        if (Yii::$container->has('sweelix\oauth2\server\interfaces\ScopeModelInterface') === false) {
            Yii::$container->set('sweelix\oauth2\server\interfaces\ScopeModelInterface', 'sweelix\oauth2\server\models\Scope');
        }
        if ((Yii::$container->has('sweelix\oauth2\server\interfaces\UserModelInterface') === false) && ($this->identityClass !== null)) {
            Yii::$container->set('sweelix\oauth2\server\interfaces\UserModelInterface', $this->identityClass);
        }
        if ($this->backend === 'redis') {
            Redis::register($app);
        }
        Oauth::register($app);

    }

    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        // use the registered identity class if not overloaded
        if (($this->identityClass === null) && (isset($app->user) === true)) {
            $this->identityClass = $app->user->identityClass;
        }
        $this->setUpDi($app);
        if (empty($this->baseEndPoint) === false) {
            $this->baseEndPoint = trim($this->baseEndPoint, '/').'/';
        }

        if ($app instanceof ConsoleApplication) {
            $this->mapConsoleControllers($app);
        } else {
            $app->getUrlManager()->addRules([
                ['verb' => 'POST', 'pattern' => $this->baseEndPoint.'token', 'route' => $this->id.'/token/index'],
                ['verb' => 'OPTIONS', 'pattern' => $this->baseEndPoint.'token', 'route' => $this->id.'/token/options'],
                ['verb' => 'GET', 'pattern' => $this->baseEndPoint.'authorize', 'route' => $this->id.'/authorize/index'],
                ['pattern' => $this->baseEndPoint.'authorize-login', 'route' => $this->id.'/authorize/login'],
                ['pattern' => $this->baseEndPoint.'authorize-application', 'route' => $this->id.'/authorize/authorize'],
                ['pattern' => $this->baseEndPoint.'authorize-error', 'route' => $this->id.'/authorize/error'],
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $status = parent::beforeAction($action);
        // override web user to avoid conflicts only when routing into this module
        if ($status === true) {
            $userConfig = [
                'class' => 'yii\web\User',
                'identityClass' => $this->identityClass,
                'enableAutoLogin' => true,
                'enableSession' => true,
                'identityCookie' => ['name' => $this->identityCookieName, 'httpOnly' => true],
                'idParam' => $this->webUserParamId,
            ];
            $userConfig = ArrayHelper::merge($userConfig, $this->webUser);

            Yii::$app->set('user', $userConfig);
        }
        return $status;
    }

    /**
     * Update controllers map to add console commands
     * @param ConsoleApplication $app
     * @since 1.0.0
     */
    protected function mapConsoleControllers(ConsoleApplication $app)
    {
        $app->controllerMap['oauth2:client'] = [
            'class' => 'sweelix\oauth2\server\commands\ClientController',
        ];
        $app->controllerMap['oauth2:scope'] = [
            'class' => 'sweelix\oauth2\server\commands\ScopeController',
        ];
        $app->controllerMap['oauth2:key'] = [
            'class' => 'sweelix\oauth2\server\commands\KeyController',
        ];

    }
}
