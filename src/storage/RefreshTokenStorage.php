<?php
/**
 * RefreshTokenStorage.php
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

use OAuth2\Storage\RefreshTokenInterface;
use sweelix\oauth2\server\models\RefreshToken;

class RefreshTokenStorage implements RefreshTokenInterface
{

    /**
     * @inheritdoc
     */
    public function getRefreshToken($refresh_token)
    {
        $refreshToken = RefreshToken::findOne($refresh_token);
        if ($refreshToken !== null) {
            $finalToken = [
                'refresh_token' => $refreshToken->id,
                'client_id' => $refreshToken->clientId,
                'user_id' => $refreshToken->userId,
                'expires' => $refreshToken->expiry,
                'scope' => implode(' ', $refreshToken->scopes),
            ];
            $refreshToken = $finalToken;
        }
        return $refreshToken;
    }

    /**
     * @inheritdoc
     */
    public function setRefreshToken($refresh_token, $client_id, $user_id, $expires, $scope = null)
    {
        $refresToken = new RefreshToken();
        $refresToken->id = $refresh_token;
        $refresToken->clientId = $client_id;
        $refresToken->userId = $user_id;
        if ($scope === null) {
            $scopes = [];
        } else {
            $scopes = explode(' ', $scope);
        }
        $refresToken->scopes = $scopes;
        $refresToken->save();
        return true;
    }

    /**
     * @inheritdoc
     */
    public function unsetRefreshToken($refresh_token)
    {
        $refreshToken = RefreshToken::findOne($refresh_token);
        if ($refreshToken !== null) {
            $refreshToken->delete();
        }
        return true;
    }
}