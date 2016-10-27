<?php
/**
 * AuthCodeStorage.php
 *
 * PHP version 5.6+
 *
 * @author pgaultier
 * @copyright 2010-2016 Ibitux
 * @license http://www.ibitux.com/license license
 * @version XXX
 * @link http://www.ibitux.com
 */

namespace sweelix\oauth2\server\storage;

use OAuth2\Storage\AuthorizationCodeInterface;
use Yii;

class AuthCodeStorage implements AuthorizationCodeInterface
{
    /**
     * @var string
     */
    private $authCodeClass;

    /**
     * @return string classname for selected interface
     * @since XXX
     */
    public function getAuthCodeClass()
    {
        if ($this->authCodeClass === null) {
            $client = Yii::createObject('sweelix\oauth2\server\interfaces\AuthCodeModelInterface');
            $this->authCodeClass = get_class($client);
        }
        return $this->authCodeClass;
    }

    public function getAuthorizationCode($code)
    {
        $authCodeClass = $this->getAuthCodeClass();
        $authCode = $authCodeClass::findOne($code);
        if ($authCode !== null) {
            $finalCode = [
                'client_id' => $authCode->clientId,
                'user_id' => $authCode->userId,
                'expires' => $authCode->expiry,
                'redirect_uri' => $authCode->redirectUri,
                'scope' => implode(' ', $authCode->scopes),
            ];
            $authCode = $finalCode;
        }
        return $authCode;
    }

    public function setAuthorizationCode($code, $client_id, $user_id, $redirect_uri, $expires, $scope = null)
    {
        $authCode = Yii::createObject('sweelix\oauth2\server\interfaces\AuthCodeModelInterface');
        $authCode->id = $code;
        $authCode->clientId = $client_id;
        $authCode->userId = $user_id;
        $authCode->redirectUri = $redirect_uri;
        $authCode->expiry = $expires;
        if ($scope === null) {
            $scopes = [];
        } else {
            $scopes = explode(' ', $scope);
        }
        $authCode->scopes = $scopes;
        $authCode->save();
        return true;
    }

    public function expireAuthorizationCode($code)
    {
        $authCodeClass = $this->getAuthCodeClass();
        $authCode = $authCodeClass::findOne($code);
        if ($authCode !== null) {
            $authCode->delete();
        }
        return true;
    }
}