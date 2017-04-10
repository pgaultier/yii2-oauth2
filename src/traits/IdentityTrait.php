<?php
/**
 * IdentityTrait.php
 *
 * PHP version 5.6+
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\traits
 */

namespace sweelix\oauth2\server\traits;

use OAuth2\Request;
use sweelix\oauth2\server\Module;
use Yii;

/**
 * Helper to build Identity management
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\traits
 * @since 1.0.0
 */
trait IdentityTrait
{

    /**
     * @var array list of restricted scopes
     */
    private $scopes = null;

    /**
     * @return array list of restricted scopes
     */
    public function getRestrictedScopes()
    {
        return $this->scopes;
    }

    /**
     * @param array $scopes list of restricted scopes
     */
    public function setRestrictedScopes($scopes)
    {
        if (is_array($scopes) === true) {
            $this->scopes = $scopes;
        } elseif (empty($scopes) === false) {
            $this->scopes = [$scopes];
        }
    }

    /**
     * @param string $token token id
     * @param string $type type of token
     * @return \sweelix\oauth2\server\interfaces\UserModelInterface
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $identity = null;
        if (($type === 'yii\filters\auth\HttpBearerAuth') || ($type === 'yii\filters\auth\QueryParamAuth')) {
            // handle only bearer tokens
            $oauthServer = Yii::createObject('OAuth2\Server');
            /* @var \OAuth2\Server $oauthServer */
            $oauthRequest = Request::createFromGlobals();
            $oauthResponse = Yii::createObject('OAuth2\Response');
            // check if token is ok
            $result = $oauthServer->verifyResourceRequest($oauthRequest, $oauthResponse);
            $tokenData = $oauthServer->getResourceController()->getToken();
            if (($result === true) && ($token === $tokenData['id_token'])) {
                $identityClass = Module::getInstance()->identityClass;
                // $identity = $identityClass::findByUsername($tokenData['user_id']);
                $identity = $identityClass::findIdentity($tokenData['user_id']);
                /* @var \sweelix\oauth2\server\interfaces\UserModelInterface $identity */
                if (empty($tokenData['scope']) === false) {
                    $identity->setRestrictedScopes(explode(' ', $tokenData['scope']));
                }
            }
        }
        return $identity;
    }

}
