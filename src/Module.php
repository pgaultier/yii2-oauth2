<?php
/**
 * Module.php
 *
 * PHP version 5.6+
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2016 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version XXX
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server
 */
namespace sweelix\oauth2\server;

use sweelix\oauth2\server\services\Redis;
use yii\base\BootstrapInterface;
use yii\base\Module as BaseModule;
use Yii;

/**
 * Oauth2 server Module definition
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2016 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version XXX
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server
 * @since XXX
 */
class Module extends BaseModule implements BootstrapInterface
{
    public $backend;
    /**
     * This user class will be used to link oauth2 authorization system with the application.
     * The class must implement \sweelix\oauth2\server\interfaces\UserInterface
     * If not defined, the Yii::$app->user->identityClass value will be used
     * @var string|array user class definition.
     */
    public $identityClass;

    /**
     * @var string change base end point
     */
    public $baseEndPoint = '';

    /**
     * @var string DateInterval TTL
     */
    public $authCodeTTL = 'PT10M';

    /**
     * @var string DateInterval TTL
     */
    public $accessTokenTTL = 'P1M';

    /**
     * @var string DateInterval TTL
     */
    public $refreshTokenTTL = 'P3M';

    /**
     * @var string DateInterval TTL
     */
    public $implicitTTL = 'PT1H';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

    /**
     * Load dataservices in container
     * @since XXX
     */
    protected function setUpDi()
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
        if (Yii::$container->has('sweelix\oauth2\server\interfaces\RefreshTokenModelInterface') === false) {
            Yii::$container->set('sweelix\oauth2\server\interfaces\RefreshTokenModelInterface', 'sweelix\oauth2\server\models\RefreshToken');
        }
        if (Yii::$container->has('sweelix\oauth2\server\interfaces\ScopeModelInterface') === false) {
            Yii::$container->set('sweelix\oauth2\server\interfaces\ScopeModelInterface', 'sweelix\oauth2\server\models\Scope');
        }
        if ((Yii::$container->has('sweelix\oauth2\server\interfaces\UserInterface') === false) && ($this->identityClass !== null)) {
            Yii::$container->set('sweelix\oauth2\server\interfaces\UserInterface', $this->identityClass);
        }

        if ($this->backend === 'redis') {
            Redis::register();
        }
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
        $this->setUpDi();
        if (empty($this->baseEndPoint) === false) {
            $this->baseEndPoint = trim($this->baseEndPoint, '/').'/';
        }

        $app->getUrlManager()->addRules([
            ['verb' => 'POST', 'pattern' => $this->baseEndPoint.'access_token', 'route' => $this->id.'/default/access-token'],
            ['verb' => 'GET', 'pattern' => $this->baseEndPoint.'authorize', 'route' => $this->id.'/default/authorize'],
        ]);
    }
}
