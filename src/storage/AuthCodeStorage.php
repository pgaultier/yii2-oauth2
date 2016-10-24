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
use sweelix\oauth2\server\models\AuthCode;

class AuthCodeStorage implements AuthorizationCodeInterface
{

    public function getAuthorizationCode($code)
    {
        $authCode = AuthCode::findOne($code);
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
        $authCode = new AuthCode();
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
        $authCode = AuthCode::findOne($code);
        if ($authCode !== null) {
            $authCode->delete();
        }
        return true;
    }
}